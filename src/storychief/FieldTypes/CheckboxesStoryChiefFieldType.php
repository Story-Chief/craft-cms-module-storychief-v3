<?php namespace storychief\storychiefv3\storychief\FieldTypes;

use  craft\base\Field;

class CheckboxesStoryChiefFieldType implements StoryChiefFieldTypeInterface
{
    public function supportedStorychiefFieldTypes()
    {
        return [
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

        $options = $field->options;

        foreach ($options as $option) {
            foreach ($fieldData as $dataValue) {
                if ($dataValue == $option['value']) {
                    $preppedData[] = $option['value'];
                }
            }
        }

        return $preppedData;
    }
}
