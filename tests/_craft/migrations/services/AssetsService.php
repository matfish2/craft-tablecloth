<?php


namespace tableclothtests\_craft\migrations\services;


class AssetsService
{
    use FieldsToElementsTrait;

    public function addFields($fields)
    {
        $s = \Craft::$app->volumes;
        $grp = $s->getVolumeByHandle('volume');
        $layout = $grp->getFieldLayout();
        $tabs = $layout->getTabs();

        $tabs[0]->setElements($this->getElementsFromFields($fields, $tabs[0]));

        $layout->setTabs($tabs);

        $grp->setFieldLayout($layout);

        return $s->saveVolume($grp);
    }
}