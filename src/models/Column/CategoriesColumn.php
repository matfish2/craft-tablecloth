<?php


namespace matfish\Tablecloth\models\Column;


use craft\elements\Category;
use craft\records\CategoryGroup;

class CategoriesColumn extends RelationsColumn
{
    protected function getOptions(): array
    {
        $field = $this->getField();

        $res = [];
        $source = explode(":", $field->getSourceOptions()[0]['value'])[1];
        $categoryGroup = CategoryGroup::find()->where("uid='{$source}'")->one();
        $categories = Category::find()->group($categoryGroup)->all();

        foreach ($categories as $category) {
            $res[] = [
                'data' => [
                    'title' => $category->title,
                    'url' => $category->getUrl(),
                ],
                'value' => $category->id
            ];
        }

        return $res;
    }
}