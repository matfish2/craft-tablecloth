<?php


namespace tableclothtests\_craft\migrations\services;


class AssetsService
{
    public function addFields($fields)
    {
        $s = \Craft::$app->volumes;
        $grp = $s->getVolumeByHandle('volume');
        $layout = $grp->getFieldLayout();
        $tabs = $layout->getTabs();

        $tabs[0]->setFields(array_merge($tabs[0]->getFields(), $fields));

        $layout->setTabs($tabs);

        $grp->setFieldLayout($layout);

        return $s->saveVolume($grp);
    }
}