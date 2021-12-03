<?php

namespace storychief\storychiefv3\controllers;

use craft;
use storychief\storychiefv3\storychief\Helpers\StoryChiefHelper;
use yii\web\Controller;
use craft\elements\Entry;
use craft\elements\User;
use storychief\storychiefv3\storychief\FieldTypes\StoryChiefFieldTypeInterface;
use storychief\storychiefv3\events\EntryPublishEvent;
use storychief\storychiefv3\events\EntryUpdateEvent;
use storychief\storychiefv3\events\EntrySaveEvent;

class WebhookController extends Controller
{
    protected $settings = null;
    protected $payload = null;
    protected $group = null;
    protected $propagationMethod = null;

    protected $allowAnonymous = true;
    public $enableCsrfValidation = false;

    public function __construct($id, $module = null)
    {
        parent::__construct($id, $module);
        $this->settings = Craft::$app->plugins->getPlugin('storychief-v3')->getSettings();
        $entry = new Entry();
        $entry->sectionId = $this->settings['section'];
        $entry->typeId = $this->settings['entry_type'];
        $this->group = $entry->site->group;
        $this->propagationMethod = $entry->section->propagationMethod;
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


            switch ($this->payload['meta']['event']) {
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
                default:
                    $response = $this->handleTestEventType();
                    break;
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
        $site = $this->_getSiteForLanguage();

        // It's a translation && craft is setup to work with propagated translations
        if (
            $site
            && $this->payload['data']['source']
            && $this->propagationMethod !== "none"
        ) {

            // if entry exists (disabled for example)
            $criteria = \craft\elements\Entry::find();
            $criteria->anyStatus();
            $criteria->id = $this->payload['data']['source']['data']['external_id'];
            $criteria->siteId = $site['id'];
            $result = $criteria->one();

            if ($result) { // if entry exists make sure it is enabled
                $entry = $result;
                $entry->setEnabledForSite(true);
                Craft::$app->elements->saveElement($entry);
            } else { // create it
                $source_site = $this->_getSiteForLanguage($this->payload['data']['source']['data']['language']);
                $criteria = \craft\elements\Entry::find();
                $criteria->id = $this->payload['data']['source']['data']['external_id'];
                $criteria->siteId = $source_site['id'];
                $entry = $criteria->one();

                $entry->siteId = $site['id'];
                $entry->setEnabledForSite(true);
                Craft::$app->elements->saveElement($entry);
            }

            $criteria = \craft\elements\Entry::find();
            $criteria->id = $this->payload['data']['source']['data']['external_id'];
            $criteria->siteId = $site['id'];
            $result = $criteria->one();
            $entry = $result;
        } else {
            $entry = new Entry();
            $entry->sectionId = $this->settings['section'];
            $entry->typeId = $this->settings['entry_type'];

            if ($this->_isLanguageSetInPayload() && $site) {
                $entry->siteId = $site['id'];
            }
        }

        // Set a default slug, will be overwritten in _map if an SEO slug is set.
        $entry->slug = craft\helpers\ElementHelper::generateSlug($this->payload['data']['title']);

        // Map all other fields
        $entry = $this->_map($entry);

        // Trigger event to alter the entry before saving
        $this->trigger(
            'beforeEntryPublish',
            new EntryPublishEvent(
                [
                    'payload' => $this->payload,
                    'settings' => $this->settings,
                    'entry' => $entry,
                ]
            )
        );

        Craft::$app->elements->saveElement($entry);

        // Trigger after publish event.
        $this->trigger('afterEntryPublish', new EntrySaveEvent(['entry' => $entry]));

        return $this->_appendMac([
            'id'        => $entry->id,
            'permalink' => $entry->getUrl(),
        ]);
    }

    protected function handleUpdateEventType()
    {
        $criteria = \craft\elements\Entry::find();
        $criteria->id = $this->payload['data']['external_id'];
        if ($this->_isLanguageSetInPayload() && $site = $this->_getSiteForLanguage()) {
            $criteria->siteId = $site['id'];
        }
        $entry = $criteria->one();

        // maybe the blog post was deleted from Craft
        if (!$entry) {
            return $this->handlePublishEventType();
        }

        $entry = $this->_map($entry);

        // Trigger event to alter the entry before saving
        $this->trigger(
            'beforeEntryUpdate',
            new EntryUpdateEvent(
                [
                    'payload' => $this->payload,
                    'settings' => $this->settings,
                    'entry' => $entry,
                ]
            )
        );

        Craft::$app->elements->saveElement($entry);

        // Trigger after update event.
        $this->trigger('afterEntryUpdate', new EntrySaveEvent(['entry' => $entry]));

        return $this->_appendMac([
            'id'        => $entry->id,
            'permalink' => $entry->getUrl(),
        ]);
    }

    protected function handleDeleteEventType()
    {
        $criteria = \craft\elements\Entry::find();
        $criteria->id = $this->payload['data']['external_id'];
        if ($this->_isLanguageSetInPayload() && $site = $this->_getSiteForLanguage()) {
            $criteria->siteId = $site['id'];
        }
        $entry = $criteria->one();

        $entry->setEnabledForSite(false);
        Craft::$app->elements->saveElement($entry);

        // hard delete if all versions are disabled
        $criteria = \craft\elements\Entry::find();
        $criteria->id = $this->payload['data']['external_id'];
        $entry = $criteria->one();
        if (!$entry) {
            Craft::$app->elements->deleteElementById($this->payload['data']['external_id']);
        }

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
                $entry->authorId = $user->id;
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
            if (empty($scHandle)) {
                continue;
            }
            $field = Craft::$app->fields->getFieldByHandle($fieldHandle);
            $class = StoryChiefHelper::getStoryChiefFieldClass($field);
            if (!class_exists($class)) {
                continue;
            }

            $value = $this->_filterPayloadData($scHandle);
            if (!$value) {
                continue;
            }

            $scField = new $class();
            if ($scField instanceof StoryChiefFieldTypeInterface) {
                $entry->setFieldValue($fieldHandle, $scField->prepFieldData($field, $value));
            }
        }

        return $entry;
    }

    private function _appendMac($response)
    {
        $key = $this->settings['key'];
        $response['mac'] = hash_hmac('sha256', json_encode($response), $key);

        return $response;
    }

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

    private function _isLanguageSetInPayload()
    {
        return (
            isset($this->payload['data']['language'])
            && $this->payload['data']['language']
        );
    }

    private function _getSiteForLanguage($language = null)
    {
        return (new \craft\db\Query())
            ->select(['id'])
            ->from('{{%sites}}')
            ->where([
                'language' => $language ?: $this->payload['data']['language'],
                'groupId' => $this->group->id])
            ->one();
    }
}
