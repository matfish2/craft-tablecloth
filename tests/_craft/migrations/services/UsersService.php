<?php


namespace tableclothtests\_craft\migrations\services;


use Craft;
use craft\elements\User;
use craft\models\FieldLayout;

class UsersService
{
    public function addFields($fields)
    {
        $layout = new FieldLayout();
        $layout->type = User::class;
        $layout->reservedFieldHandles = [
            'groups',
            'photo',
        ];

        $layout->setTabs([
            [
                'name'=>'Content'
            ]
        ]);

        $tabs = $layout->getTabs();

        $tabs[0]->setFields(array_merge($tabs[0]->getFields(), $fields));

        $layout->setTabs($tabs);

        return Craft::$app->users->saveLayout($layout);
    }
}