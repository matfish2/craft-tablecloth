<?php


namespace tableclothtests\_craft\migrations\services;


use craft\records\User;

abstract class FeedGenerator
{
    public function generate() : array {
         $feed = [
            'page'=>1,
            'data'=>[]
        ];

        $res = [];

        for ($i=0; $i<100;$i++) {
            $f = $this->getNativeFieldsData();

            foreach (FieldsList::$list as $fieldClass) {
                $factory = new FieldFactory($fieldClass);
                $fieldData = $factory->getFieldData();

                $feeder = $factory->getFeederClass();
                $f[$fieldData['handle']] = $feeder->get();
            }

            $res[] = $f;
        }

        $feed['data'] = $res;

        return $feed;
    }

    abstract protected function getNativeFieldsData() : array;

}