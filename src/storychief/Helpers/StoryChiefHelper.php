<?php namespace storychief\storychiefv3\storychief\Helpers;

use storychief\storychiefv3\storychief\FieldTypes\RichTextStoryChiefFieldType;

class StoryChiefHelper
{
    public static function parseBoolean($value)
    {
        if (is_array($value)) {
            $value = array_shift($value);
        }

        $result = filter_var($value, FILTER_VALIDATE_BOOLEAN);

        // Also check for translated values of boolean-like terms
        if (strtolower($value) === Craft::t('yes')) {
            $result = true;
        }

        if (strtolower($value) === Craft::t('on')) {
            $result = true;
        }

        if (strtolower($value) === Craft::t('open')) {
            $result = true;
        }


        if (strtolower($value) === Craft::t('no')) {
            $result = false;
        }

        if (strtolower($value) === Craft::t('off')) {
            $result = false;
        }

        if (strtolower($value) === Craft::t('closed')) {
            $result = false;
        }

        return $result;
    }

    public static function getStoryChiefFieldClass($field)
    {
        if (!$field) {
            return null;
        }

        if (class_exists(\craft\redactor\Field::class) && $field instanceof \craft\redactor\Field) {
            return RichTextStoryChiefFieldType::class;
        }

        return str_replace('craft\\fields', '\\storychief\\storychiefv3\\storychief\\FieldTypes', get_class($field)) . 'StoryChiefFieldType';
    }
}
