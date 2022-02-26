<?php


namespace tableclothtests\_craft\migrations\services;


class TagsService
{
    public function addFields($fields)
    {
        $s = \Craft::$app->tags;
        $grp = $s->getTagGroupByHandle('tags');
        $layout = $grp->getFieldLayout();
        $tabs = $layout->getTabs();

        $tabs[0]->setFields(array_merge($tabs[0]->getFields(), $fields));

        $layout->setTabs($tabs);

        $grp->setFieldLayout($layout);

        return $s->saveTagGroup($grp);
    }
}