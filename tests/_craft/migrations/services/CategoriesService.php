<?php


namespace tableclothtests\_craft\migrations\services;


class CategoriesService
{
    public function addFields($fields)
    {
        $s = \Craft::$app->categories;
        $grp = $s->getGroupByHandle('categories');
        $layout = $grp->getFieldLayout();
        $tabs = $layout->getTabs();

        $tabs[0]->setFields(array_merge($tabs[0]->getFields(), $fields));

        $layout->setTabs($tabs);

        $grp->setFieldLayout($layout);

        return $s->saveGroup($grp);
    }
}