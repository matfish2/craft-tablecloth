<?php


namespace tableclothtests\_craft\migrations\services;

use Craft;
use craft\records\FieldGroup;

class FieldsService
{

    public function add(array $data)
    {
        if ($field = Craft::$app->fields->getFieldByHandle($data['handle'])) {
            return $field;
        }


        $groupId = $this->getFieldGroupId();
        $fieldsService = Craft::$app->getFields();

        $params = array_merge($data,['groupId'=>$groupId]);

        $field = $fieldsService->createField($params);

        if (!$fieldsService->saveField($field)) {
            throw new \Exception("Failed to save {$data['handle']} field");
        }

        return $field;
    }

    public function remove($handle): bool
    {
        $field = Craft::$app->fields->getFieldByHandle($handle);;

        if ($field) {
            return Craft::$app->fields->deleteField($field);
        }

        return false;
    }

    private function getFieldGroupId()
    {
        return FieldGroup::find()->one()->id;
    }
}