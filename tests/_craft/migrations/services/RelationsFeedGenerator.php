<?php


namespace tableclothtests\_craft\migrations\services;

use craft\elements\User;

class RelationsFeedGenerator
{
    public function generate(string $el) {
        $els  = $el::find()->all();

        $res = [];

        foreach ($els as $e) {
            $f = [
                'id'=>$e->id,
                'title'=>$el===User::class ? $e->username :  $e->title
            ];

            foreach (FieldsList::$list as $fieldClass) {
                $factory = new FieldFactory($fieldClass);
                $fieldData = $factory->getFieldData();

                $feeder = $factory->getFeederClass();
                $f[$fieldData['handle']] = $feeder->get();
            }

            $res[] = $f;
        }

        return $res;
    }
}