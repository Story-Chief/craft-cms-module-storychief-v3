<?php
namespace storychief\storychiefv3\variables;

use storychief\storychiefv3\storychief\FieldTypes\StoryChiefFieldTypeInterface;
use craft;

class StoryChiefVariable
{
    public function getStoryChiefSections()
    {
        $sections = [];
        foreach (\Craft::$app->sections->getAllSections() as $section) {
            if ($section->type === 'channel') {
                $sections[] = [
                    'label' => $section->name,
                    'value' => $section->id,
                ];
            }
        }

        return $sections;
    }

    public function getAllStoryChiefFields()
    {
        $default_fields = [
            [
                'label' => 'Content',
                'name'  => 'content',
                'type'  => 'richtext',
            ],
            [
                'label' => 'Excerpt',
                'name'  => 'excerpt',
                'type'  => 'textarea',
            ],
            [
                'label' => 'Featured image',
                'name'  => 'featured_image',
                'type'  => 'image',
            ],
            [
                'label' => 'Tags',
                'name'  => 'tags',
                'type'  => 'tags',
            ],
            [
                'label' => 'Categories',
                'name'  => 'categories',
                'type'  => 'categories',
            ],
            [
                'label' => 'SEO Title',
                'name'  => 'seo_title',
                'type'  => 'text',
            ],
            [
                'label' => 'SEO Description',
                'name'  => 'seo_description',
                'type'  => 'textarea',
            ],
        ];
        
        $settings = Craft::$app->plugins->getPlugin('storychief-v3')->getSettings();
        $custom_fields = $settings['custom_field_definitions'];

        return array_merge($default_fields, $custom_fields);
    }

    public function getStoryChiefFieldOptions($fieldHandle)
    {
        $field = \Craft::$app->fields->getFieldByHandle($fieldHandle);
        $class = str_replace('craft\\fields', '\\storychief\\storychiefv3\\storychief\\FieldTypes', get_class($field)).'StoryChiefFieldType';
        $allFields = $this->getAllStoryChiefFields();
        $options = [];
        if (class_exists($class)) {
            $field = new $class();
            if ($field instanceof StoryChiefFieldTypeInterface) {
                $supportedTypes = $field->supportedStorychiefFieldTypes();
                foreach ($allFields as $item) {
                    if (in_array($item['type'], $supportedTypes)) {
                        $options[] = [
                            'label' => $item['label'],
                            'value' => $item['name'],
                        ];
                    }
                }
            }
        }

        return empty($options) ? null : $options;
    }

    public function getStoryChiefAuthorOptions()
    {
        return [
            [
                'label' => 'Don\'t import',
                'value' => '',
            ],
            [
                'label' => 'Import',
                'value' => 'import',
            ],
            [
                'label' => 'Import or create new',
                'value' => 'create',
            ]
        ];
    }

    public function getStoryChiefEntryTypes($sectionID)
    {
        $entryTypes = [];
        foreach (\Craft::$app->sections->getEntryTypesBySectionId($sectionID) as $entryType) {
            $entryTypes[] = [
                'label' => $entryType->name,
                'value' => $entryType->id,
            ];
        }

        return $entryTypes;
    }

    public function getStoryChiefContentFields($entryTypeID)
    {
        $fieldDefinitions = [];

        $entryType = \Craft::$app->sections->getEntryTypeById($entryTypeID);


        $fields = $entryType->getFieldLayout()->getFields();

        foreach ($fields as $field) {
            $fieldDefinition = $field->getAttributes(['id', 'name', 'handle']);
            $fieldDefinition['required'] = $field->required === '1';
            $fieldDefinitions[] = $fieldDefinition;
        }
        return $fieldDefinitions;
    }
}
