<?php namespace storychief\storychiefv3\storychief\FieldTypes;

use  craft\base\Field;

class LightswitchStoryChiefFieldType implements StoryChiefFieldTypeInterface
{
    public function supportedStorychiefFieldTypes()
    {
        return [
            'select',
            'radio',
            'checkbox'
        ];
    }

    public function prepFieldData(Field $field, $fieldData)
    {
        $preppedData = StoryChiefHelper::parseBoolean($fieldData);
        return $preppedData;
    }
}
