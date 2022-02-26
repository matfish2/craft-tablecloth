<?php


namespace tableclothtests\_craft\Fields\Feeders;


use craft\elements\Tag;
use tableclothtests\_craft\migrations\services\FakerService;

class TagsFeeder extends FieldFeeder
{

    /**
     * @throws \Exception
     */
    public function get($options = null)
    {
        $tags = Tag::find()->all();
        $opts = array_map(function ($tag) {
            return $tag->title;
        }, $tags);

        return FakerService::arrayElements($opts, random_int(1, 4));
    }
}