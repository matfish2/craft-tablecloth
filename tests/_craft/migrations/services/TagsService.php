<?php


namespace tableclothtests\_craft\migrations\services;


class TagsService
{
    use FieldsToElementsTrait;

    public function addFields($fields)
    {
        $s = \Craft::$app->tags;
        $grp = $s->getTagGroupByHandle('tags');
        $layout = $grp->getFieldLayout();
        $tabs = $layout->getTabs();

        $tabs[0]->setElements($this->getElementsFromFields($fields, $tabs[0]));

        $layout->setTabs($tabs);

        $grp->setFieldLayout($layout);

        return $s->saveTagGroup($grp);
    }
}