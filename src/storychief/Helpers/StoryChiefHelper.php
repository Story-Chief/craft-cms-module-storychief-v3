<?php namespace storychief\storychiefv3\storychief\Helpers;

use Craft;
use craft\fields\Assets;
use craft\fields\Categories;
use craft\fields\Checkboxes;
use craft\fields\Dropdown;
use craft\fields\Entries;
use craft\fields\Lightswitch;
use craft\fields\MultiSelect;
use craft\fields\PlainText;
use craft\fields\RadioButtons;
use craft\fields\Tags;
use storychief\storychiefv3\storychief\FieldTypes\AssetsStoryChiefFieldType;
use storychief\storychiefv3\storychief\FieldTypes\CategoriesStoryChiefFieldType;
use storychief\storychiefv3\storychief\FieldTypes\CheckboxesStoryChiefFieldType;
use storychief\storychiefv3\storychief\FieldTypes\DropdownStoryChiefFieldType;
use storychief\storychiefv3\storychief\FieldTypes\EntriesStoryChiefFieldType;
use storychief\storychiefv3\storychief\FieldTypes\LightswitchStoryChiefFieldType;
use storychief\storychiefv3\storychief\FieldTypes\MultiSelectStoryChiefFieldType;
use storychief\storychiefv3\storychief\FieldTypes\PlainTextStoryChiefFieldType;
use storychief\storychiefv3\storychief\FieldTypes\RadioButtonsStoryChiefFieldType;
use storychief\storychiefv3\storychief\FieldTypes\RichTextStoryChiefFieldType;
use storychief\storychiefv3\storychief\FieldTypes\TagsStoryChiefFieldType;

class StoryChiefHelper
{
    public static function parseBoolean($value)
    {
        if (is_array($value)) {
            $value = array_shift($value);
        }

        $result = filter_var($value, FILTER_VALIDATE_BOOLEAN);

        // Also check for translated values of boolean-like terms
        if (strtolower($value) === Craft::t('app', 'yes')) {
            $result = true;
        }

        if (strtolower($value) === Craft::t('app', 'on')) {
            $result = true;
        }

        if (strtolower($value) === Craft::t('app', 'open')) {
            $result = true;
        }


        if (strtolower($value) === Craft::t('app', 'no')) {
            $result = false;
        }

        if (strtolower($value) === Craft::t('app', 'off')) {
            $result = false;
        }

        if (strtolower($value) === Craft::t('app', 'closed')) {
            $result = false;
        }

        return $result;
    }

    public static function getStoryChiefFieldClass($field)
    {
        if (!$field) {
            return null;
        }

        switch (get_class($field)) {
            // Default available fields
            case Assets::class:
                return AssetsStoryChiefFieldType::class;
            case Categories::class:
                return CategoriesStoryChiefFieldType::class;
            case Tags::class:
                return TagsStoryChiefFieldType::class;
            case Entries::class:
                return EntriesStoryChiefFieldType::class;
            case Checkboxes::class:
                return CheckboxesStoryChiefFieldType::class;
            case RadioButtons::class:
                return RadioButtonsStoryChiefFieldType::class;
            case Dropdown::class:
                return DropdownStoryChiefFieldType::class;
            case Lightswitch::class:
                return LightswitchStoryChiefFieldType::class;
            case MultiSelect::class:
                return MultiSelectStoryChiefFieldType::class;
            case PlainText::class:
                return PlainTextStoryChiefFieldType::class;

            // Plugin installed fields
            case 'craft\redactor\Field':
                return RichTextStoryChiefFieldType::class;
            default:
                return null;
        }
    }
}
