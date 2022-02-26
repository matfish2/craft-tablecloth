<?php


namespace tableclothtests\_craft\migrations\services;


use craft\commerce\elements\Variant;
use craft\commerce\Plugin as Commerce;
use craft\models\FieldLayout;

class ProductsService
{
    public function addFields($fields)
    {
        $types = Commerce::getInstance()->productTypes->getAllProductTypes();

        foreach ($types as $type) {
            $layout = $type->getFieldLayout();

            $tabs = $layout->getTabs();

            if (count($tabs) === 0) {
                $layout->setTabs([
                    [
                        'name' => 'Content'
                    ]
                ]);

                $tabs = $layout->getTabs();

            }

            $tabs[0]->setFields(array_merge($tabs[0]->getFields(), $fields));

            $layout->setTabs($tabs);

            $type->setFieldLayout($layout);

            if ($type->hasVariants) {
                $vlayout = new FieldLayout();
                $vlayout->type = Variant::class;

                $vlayout->setTabs([
                    [
                        'name' => 'Content'
                    ]
                ]);

                $vtabs = $vlayout->getTabs();


                $vtabs[0]->setFields(array_merge($vtabs[0]->getFields(), $fields));

                $vlayout->setTabs($vtabs);
                $behavior = $type->getBehavior('variantFieldLayout');
                $behavior->setFieldLayout($vlayout);
            }

            Commerce::getInstance()->productTypes->saveProductType($type);

        }

        return true;
    }
}