<?php


namespace tableclothtests\_craft\Fields\Creators;


use Craft;
use craft\elements\Category;
use craft\models\CategoryGroup;
use craft\models\CategoryGroup_SiteSettings;

class CategoriesCreator extends FieldCreator
{
    public function getFieldData(): array
    {
        $catgroup = $this->generateCategories();

        return [
            $this->settingsKey => [
                'source' => 'group:' . $catgroup->uid
            ]
        ];
    }

    private function generateCategories(): CategoryGroup
    {
        $catGroup = Craft::$app->categories->getGroupByHandle('categories');

        if ($catGroup) {
            return $catGroup;
        }

        $siteSettings = new CategoryGroup_SiteSettings([
            'siteId' => Craft::$app->sites->getPrimarySite()->id,
            'hasUrls' => true,
            'uriFormat' => 'category/{slug}',
            'template' => 'category/_entry',
        ]);

        $categoryGroup = new CategoryGroup([
                'name' => 'Categories',
                'handle' => 'categories',
            ]
        );

        $categoryGroup->setSiteSettings([1 => $siteSettings]);

        Craft::$app->categories->saveGroup($categoryGroup);

        $list = ['Sports', 'Entertainment', 'Politics', 'Finance', 'Games'];

        foreach ($list as $category) {
            $c = new Category([
                'groupId' => $categoryGroup->id,
                'title' => $category,
            ]);

            Craft::$app->elements->saveElement($c);
        }

        return $categoryGroup;
    }
}