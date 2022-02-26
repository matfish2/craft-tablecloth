<?php


namespace tableclothtests\_craft\Fields\Feeders;


use craft\records\User;
use tableclothtests\_craft\migrations\services\FakerService;

class UsersFeeder extends FieldFeeder
{

    public function get($options = null)
    {
        $users = User::find()->all();
        $opts = array_map(static function($user) {
            return $user->id;
        }, $users);

        return FakerService::arrayElements($opts,random_int(1,2));
    }
}