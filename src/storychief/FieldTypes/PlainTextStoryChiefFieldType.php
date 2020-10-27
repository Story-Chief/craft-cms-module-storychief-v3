<?php namespace storychief\storychiefv3\storychief\FieldTypes;

use  craft\base\Field;

class PlainTextStoryChiefFieldType implements StoryChiefFieldTypeInterface
{
    public function supportedStorychiefFieldTypes()
    {
        return [
            'text',
            'textarea',
            'excerpt'
        ];
    }

    public function prepFieldData(Field $field, $fieldData)
    {
        $preppedData = $fieldData;
        return $preppedData;
    }
}
