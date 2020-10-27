<?php namespace storychief\storychiefv3\storychief\FieldTypes;

use Craft;
use craft\helpers\Db;
use  craft\base\Field;
use craft\elements\Category;

class CategoriesStoryChiefFieldType implements StoryChiefFieldTypeInterface
{
    public function supportedStorychiefFieldTypes()
    {
        return [
            'categories',
            'select',
            'checkbox'
        ];
    }

    public function prepFieldData(Field $field, $fieldData)
    {
        $preppedData = [];
        $categoryGroupUID = str_replace('group:', '', $field->source);
        $categoryGroup =  (new \craft\db\Query())
        ->select(['id'])
        ->from('categorygroups')
        ->where(['uid' => $categoryGroupUID])
        ->one();

        $categoryGroupID = $categoryGroup['id'];


        $limit = count($fieldData);
        if (isset($field->settings['limit']) && $field->settings['limit']) {
            $limit = min($limit, $field->settings['limit']);
        }
        $i = 0;
        while ($i < $limit) {
            $categoryName = $fieldData[$i];
            
            $criteria = Category::find();
            $criteria->groupId = $categoryGroupID;
            $criteria->title = Db::escapeParam($categoryName);
            $category = $criteria->one();


            if (!$category) {
                $category = new Category();
                $category->groupId = $categoryGroupID;
                $category->title = $categoryName;

                Craft::$app->elements->saveElement($category);
            }
            $preppedData[] = $category->id;

            $i++;
        }

        return $preppedData;
    }
}
