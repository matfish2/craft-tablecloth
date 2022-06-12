<?php


namespace tableclothtests\_craft\migrations\services;


use Craft;
use craft\elements\User;
use craft\models\FieldLayout;

class UsersService
{
    use FieldsToElementsTrait;

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

        $tabs[0]->setElements($this->getElementsFromFields($fields,$tabs[0]));

        $layout->setTabs($tabs);

        return Craft::$app->users->saveLayout($layout);
    }
}