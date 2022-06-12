<?php


namespace tableclothtests\_craft\migrations\services;


use craft\fieldlayoutelements\CustomField;
use craft\models\FieldLayoutTab;

trait FieldsToElementsTrait
{
    protected function getElementsFromFields($fields, FieldLayoutTab $tab) : array {
         $existingElements = [];

        foreach ($tab->getElements() as $el) {
            if ($el instanceof CustomField) {
                $existingElements[] = [
                    'type' => CustomField::class,
                    'fieldUid' => $el->getField()->uid,
                    'required' => false
                ];
            }
        }

          $elements = array_map(static function ($field) {
            return [
                'type' => CustomField::class,
                'fieldUid' => $field->uid,
                'required' => false
            ];
        }, $fields);

        return array_merge($existingElements,$elements);
    }
}