<?php


namespace tableclothtests\_craft\migrations\services;


class CategoriesService
{
    use FieldsToElementsTrait;

    public function addFields($fields)
    {
        $s = \Craft::$app->categories;
        $grp = $s->getGroupByHandle('categories');
        $layout = $grp->getFieldLayout();
        $tabs = $layout->getTabs();

        $tabs[0]->setElements($this->getElementsFromFields($fields, $tabs[0]));

        $layout->setTabs($tabs);

        $grp->setFieldLayout($layout);

        return $s->saveGroup($grp);
    }
}