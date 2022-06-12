<?php


namespace tableclothtests\_craft\migrations\services;


use Craft;
use craft\fieldlayoutelements\CustomField;
use matfish\Blogify\Handles;

class EntryTypeService
{
    use FieldsToElementsTrait;

    public function addFields($fields)
    {
        $section = Craft::$app->sections->getSectionByHandle('posts');
        $entryType = $section->getEntryTypes()[0];
        $layout = $entryType->getFieldLayout();

        $tabs = $layout->getTabs();

        $tabs[0]->setElements($this->getElementsFromFields($fields, $tabs[0]));

        $layout->setTabs($tabs);

        $entryType->setFieldLayout($layout);

        return Craft::$app->sections->saveEntryType($entryType);
    }
}