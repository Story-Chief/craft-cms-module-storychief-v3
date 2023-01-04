<?php

namespace storychief\storychief\storychief\FieldTypes;

use  craft\base\Field;

interface StoryChiefFieldTypeInterface
{
    public function supportedStorychiefFieldTypes();

    public function prepFieldData(Field $field, $fieldData);
}
