<?php
namespace storychief\storychiefv3\controllers;

use craft;
use yii\web\Controller;
use craft\elements\Entry;
use craft\elements\User;
use storychief\storychiefv3\storychief\FieldTypes\StoryChiefFieldTypeInterface;
use storychief\storychiefv3\events\EntrySaveEvent;

class WebhookController extends Controller
{
    const EVENT_AFTER_ENTRY_PUBLISH = "afterEntryPublish";
    const EVENT_AFTER_ENTRY_UPDATE = "afterEntryUpdate";

    protected $allowAnonymous = true;
    protected $settings = null;
    protected $event = null;
    protected $payload = null;
    public $enableCsrfValidation = false;

    public function __construct($id, $module = null)
    {
        parent::__construct($id, $module);
        $this->settings = Craft::$app->plugins->getPlugin('storychief-v3')->getSettings();
    }

    public function actionCallback()
    {
        try {
            $body = @file_get_contents('php://input');
            $this->payload = json_decode($body, true);

            if (!$this->validateCallback()) {
                Craft::$app->getResponse()->setStatusCode(400);
                return $this->asJson('Callback failed validation');
            }

            $this->event = $this->payload['meta']['event'];

            switch ($this->event) {
                case 'publish':
                    $response = $this->handlePublishEventType();
                    break;
                case 'update':
                    $response = $this->handleUpdateEventType();
                    break;
                case 'delete':
                    $response = $this->handleDeleteEventType();
                    break;
                case 'test':
                    $response = $this->handleTestEventType();
                    break;
                default:
                    $response = $this->handleMissingEventType();
            }
            return $this->asJson($response);
        } catch (\Exception $e) {
            Craft::$app->getResponse()->setStatusCode(500);
            return $this->asJson([
                'message' => $e->getMessage(),
                'code'    => $e->getCode(),
                'debug'   => $e->getFile() . ': line ' . $e->getLine(),
                'trace'   => $e->getTraceAsString()
            ]);
        }
    }

    protected function validateCallback()
    {
        $json = $this->payload;
        if (!is_array($json)) {
            return false;
        }

        $key = $this->settings['key'];
        $givenMac = $json['meta']['mac'];
        unset($json['meta']['mac']);
        $calcMac = hash_hmac('sha256', json_encode($json), $key);

        return hash_equals($givenMac, $calcMac);
    }

    protected function handlePublishEventType()
    {
        $section = $this->settings['section'];
        $entry_type = $this->settings['entry_type'];

        $entry = new Entry();
        $entry->sectionId = $section;
        $entry->typeId = $entry_type;

        $entry = $this->_map($entry);

        // Set language
        // If language is set and there more than one language configure on CRAFT
        if (
            isset($this->payload['data']['language']) &&
            $this->payload['data']['language'] &&
            is_array($entry->site->group->sites) &&
            sizeof($entry->site->group->sites) > 1) {
            $site =  (new \craft\db\Query())
            ->select(['id'])
            ->from('sites')
            ->where(['language' => $this->payload['data']['language'], 'groupId' => $entry->site->group->id])
            ->one();

            $entry->siteId = $site['id'];
        }
        if ($this->payload['data']['source']) {
            $entry = $this->handlePublishTranslation();
        }
        Craft::$app->elements->saveElement($entry);

        // Trigger after publish event.
        $event = new EntrySaveEvent([
            'entry' => $entry
        ]);
        $this->trigger(self::EVENT_AFTER_ENTRY_PUBLISH, $event);

        return $this->_appendMac([
            'id'        => $entry->id,
            'permalink' => $entry->getUrl(),
        ]);
    }

    protected function handlePublishTranslation()
    {
        $site = (new \craft\db\Query())
            ->select(['id'])
            ->from('sites')
            ->where(['language' => $this->payload['data']['language']])
            ->one();

        $criteria = \craft\elements\Entry::find();
        $criteria->id = $this->payload['data']['source']['data']['external_id'];
        $criteria->siteId = $site['id'];
        $entry = $criteria->one();

        $entry = $this->_map($entry);

        return $entry;
    }

    protected function handleUpdateEventType()
    {
        $criteria = \craft\elements\Entry::find();
        $criteria->id = $this->payload['data']['external_id'];
        $entry = $criteria->first();

        // Set language
        if (
            isset($this->payload['data']['language']) &&
            $this->payload['data']['language'] &&
            is_array($entry->site->group->sites) &&
            sizeof($entry->site->group->sites) > 1) {
            $site =  (new \craft\db\Query())
            ->select(['id'])
            ->from('sites')
            ->where(['language' => $this->payload['data']['language'], 'groupId' => $entry->site->group->id])
            ->one();

            $criteria = \craft\elements\Entry::find();
            $criteria->id = $this->payload['data']['external_id'];
            $criteria->siteId = $site['id'];
            $entry = $criteria->first();
        }

        $entry = $this->_map($entry);
        Craft::$app->elements->saveElement($entry);

        // Trigger after update event.
        $event = new EntrySaveEvent([
            'entry' => $entry
        ]);
        $this->trigger(self::EVENT_AFTER_ENTRY_UPDATE, $event);

        return $this->_appendMac([
            'id'        => $entry->id,
            'permalink' => $entry->getUrl(),
        ]);
    }

    protected function handleDeleteEventType()
    {
        Craft::$app->getElements()->deleteElementById($this->payload['data']['external_id']);

        return '';
    }

    protected function handleTestEventType()
    {
        $storyChiefPlugin = Craft::$app->plugins->getPlugin('storychief-v3');
        if (isset($this->payload['data']['custom_fields']['data'])) {
            Craft::$app->plugins->savePluginSettings($storyChiefPlugin, [
                'custom_field_definitions' => $this->payload['data']['custom_fields']['data'],
            ]);
        } else {
            Craft::$app->plugins->savePluginSettings($storyChiefPlugin, [
                'custom_field_definitions' => [],
            ]);
        }

        return '';
    }

    protected function handleMissingEventType()
    {
        return '';
    }

    // map data to entry
    private function _map(Entry $entry)
    {
        $mapping = $this->settings['mapping'];

        // Set author
        if (isset($mapping['author']) && $mapping['author']) {
            // find author
            $user = User::find()->email($this->payload['data']['author']['data']['email'])->one();
            if (is_null($user) && $mapping['author'] === 'create') {
                $authorData = $this->payload['data']['author']['data'];

                $user = new User();
                $user->username = strtolower($authorData['first_name'] . '.' . $authorData['last_name']);
                $user->firstName = $authorData['first_name'];
                $user->lastName = $authorData['last_name'];
                $user->email = $authorData['email'];
                $user->passwordResetRequired = false;
                $user->photoId = null;

                Craft::$app->getElements()->saveElement($user, false);
            }
            if (!is_null($user)) {
                $entry->authorId =  $user->id;
            }
        }
        unset($mapping['author']);

        // Set slug
        if (isset($this->payload['data']['seo_slug']) && !empty($this->payload['data']['seo_slug'])) {
            $entry->slug = $this->payload['data']['seo_slug'];
        }

        // Set title
        $entry->title = $this->payload['data']['title'];

        // map other fields
        foreach ($mapping as $fieldHandle => $scHandle) {
            if (!empty($scHandle)) {
                $field = Craft::$app->fields->getFieldByHandle($fieldHandle);
                $class = str_replace('craft\\fields', '\\storychief\\storychiefv3\\storychief\\FieldTypes', get_class($field)).'StoryChiefFieldType';
                if (class_exists($class)) {
                    $value = $this->_filterPayloadData($scHandle);
                    if ($value) {
                        $scField = new $class();
                        if ($scField instanceof StoryChiefFieldTypeInterface) {
                            $entry->setFieldValue($fieldHandle, $scField->prepFieldData($field, $value));
                        }
                    }
                }
            }
        }

        return $entry;
    }

    // append MAC to a response
    private function _appendMac($response)
    {
        $key = $this->settings['key'];
        $response['mac'] = hash_hmac('sha256', json_encode($response), $key);

        return $response;
    }

    // returns string value or array of strings values (select or checkboxes)
    private function _filterPayloadData($scHandle)
    {
        switch ($scHandle) {
            case 'featured_image':
                // returns image url
                if (isset($this->payload['data'][$scHandle]['data']['sizes']['full'])) {
                    return $this->payload['data'][$scHandle]['data']['sizes']['full'];
                }

                return null;
            case 'categories':
            case 'tags':
                // returns array of values
                if (isset($this->payload['data'][$scHandle]['data'])) {
                    return array_map(function ($v) {
                        return $v['name'];
                    }, $this->payload['data'][$scHandle]['data']);
                }

                return null;
            case 'title':
            case 'content':
            case 'excerpt':
            case 'seo_title':
            case 'seo_description':
                // returns value
                if (isset($this->payload['data'][$scHandle])) {
                    return $this->payload['data'][$scHandle];
                }

                return null;

            default:
                // returns value or array of values
                return $this->_filterPayloadCustomData($scHandle);
        }
    }

    // returns string value or array of strings values (select or checkboxes)
    private function _filterPayloadCustomData($scHandle)
    {
        $cfd = $this->settings['custom_field_definitions'];
        $found_cfd_key = array_search($scHandle, array_column($cfd, 'name'));
        $found_value_key = array_search($scHandle, array_column($this->payload['data']['custom_fields'], 'key'));
        if ($found_cfd_key === false || $found_value_key === false) {
            return null;
        }

        switch ($cfd[$found_cfd_key]['type']) {
            case 'select':
            case 'checkbox':
                return explode(',', $this->payload['data']['custom_fields'][$found_value_key]['value']);
            default:
                return $this->payload['data']['custom_fields'][$found_value_key]['value'];
        }
    }
}
