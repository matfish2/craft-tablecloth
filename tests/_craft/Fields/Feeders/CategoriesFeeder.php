<?php


namespace tableclothtests\_craft\Fields\Feeders;


use Craft;
use craft\elements\Category;
use tableclothtests\_craft\migrations\services\FakerService;

class CategoriesFeeder extends FieldFeeder
{

    /**
     * @throws \Exception
     */
    public function get($options = null)
    {
         $catGroup = Craft::$app->categories->getGroupByHandle('categories');
         $categories = Category::find()->group($catGroup)->all();
         $opts = array_map(static function($cat){
             return $cat->id;
         }, $categories);

         return FakerService::arrayElements($opts, random_int(1,3));
    }
}