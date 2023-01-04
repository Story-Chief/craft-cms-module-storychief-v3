<?php

namespace storychief\storychief\storychief\FieldTypes;

use craft\base\Field;
use storychief\storychief\storychief\Helpers\StoryChiefHelper;

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
