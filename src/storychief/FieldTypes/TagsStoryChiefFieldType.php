<?php namespace storychief\storychiefv3\storychief\FieldTypes;

use Craft;
use craft\base\Field;
use craft\helpers\Db;
use craft\elements\Tag;

class TagsStoryChiefFieldType implements StoryChiefFieldTypeInterface
{
    public function supportedStorychiefFieldTypes()
    {
        return [
            'tags',
            'select',
            'checkbox'
        ];
    }

    public function prepFieldData(Field $field, $fieldData)
    {
        $preppedData = [];

        if (empty($fieldData)) {
            return $preppedData;
        }
        if (!is_array($fieldData)) {
            $fieldData = array($fieldData);
        }

        $source = $field->source;
        list($type, $groupUid) = explode(':', $source);

        $tagGroup =  (new \craft\db\Query())
        ->select(['id'])
        ->from('{{%taggroups}}')
        ->where(['uid' => $groupUid])
        ->one();

        $groupId = $tagGroup['id'];


        // Find existing
        foreach ($fieldData as $tagName) {
            $criteria = Tag::find();
            $criteria->status = null;
            $criteria->groupId = $groupId;
            $criteria->title = Db::escapeParam($tagName);

            $elements = $criteria->ids();

            $preppedData = array_merge($preppedData, $elements);

            // Create the elements if not found
            if (count($elements) == 0) {
                $element = new Tag();
                $element->title = $tagName;
                $element->groupId = $groupId;

                // Save tag
                if (Craft::$app->elements->saveElement($element)) {
                    $preppedData[] = $element->id;
                }
            }
        }

        return $preppedData;
    }
}
