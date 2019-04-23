<?php namespace storychief\storychiefv3\storychief\FieldTypes;

class RichTextStoryChiefFieldType implements StoryChiefFieldTypeInterface
{
    public function supportedStorychiefFieldTypes()
    {
        return [
            'richtext',
            'text',
            'textarea',
            'excerpt'
        ];
    }

    public function prepFieldData(FieldModel $field, $fieldData)
    {
        $preppedData = $fieldData;
        return $preppedData;
    }
}
