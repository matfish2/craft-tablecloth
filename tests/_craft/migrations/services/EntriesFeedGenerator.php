<?php


namespace tableclothtests\_craft\migrations\services;


use craft\records\User;

class EntriesFeedGenerator extends FeedGenerator
{

    protected function getNativeFieldsData() : array
    {
        return [
            'title' => FakerService::sentence(),
            'postDate' => FakerService::date(),
            'author' => FakerService::arrayElement(array_map(static function ($u) {
                return $u->id;
            }, User::find()->all()))
        ];
    }
}