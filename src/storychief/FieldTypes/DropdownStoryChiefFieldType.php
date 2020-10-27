<?php namespace storychief\storychiefv3\storychief\FieldTypes;

use  craft\base\Field;

class DropdownStoryChiefFieldType implements StoryChiefFieldTypeInterface
{
    public function supportedStorychiefFieldTypes()
    {
        return [
            'select'
        ];
    }

    public function prepFieldData(Field $field, $fieldData)
    {
        $preppedData = null;

        if (empty($fieldData) || empty($fieldData[0])) {
            return null;
        }

        $options = $field->options;

        foreach ($options as $option) {
            if ($fieldData[0] == $option['value']) {
                $preppedData = $option['value'];
                break;
            }
        }

        return $preppedData;
    }
}
