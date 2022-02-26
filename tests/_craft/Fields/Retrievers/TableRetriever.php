<?php


namespace tableclothtests\_craft\Fields\Retrievers;


use craft\fields\Date;
use tableclothtests\_craft\Fields\Feeders\TableFeeder;
use tableclothtests\_craft\migrations\services\FieldFactory;

class TableRetriever extends FieldValueRetriever
{

    /**
     * @throws \Exception
     */
    public function transform($value)
    {
        $field = \Craft::$app->fields->getFieldByHandle($this->data['handle']);
        $columns = $field->columns;
        $res = [];

        foreach ($value as $row) {
            $r = [];

            foreach ($columns as $col => $val) {
                $c = $columns[$col];
                $cls = TableFeeder::$map[$c['type']];
                if ($c['type'] === 'select') {
                    $v = $row[$col];

                    $val = $v ? array_values(array_filter($c['options'], function ($col) use ($v) {
                        return $col['value'] === $v;
                    }))[0] : [
                        'label' => '',
                        'value' => null
                    ];
                } else {
                    $val = is_null($row[$col]) ? null : (new FieldFactory($cls))->getRetrieverClass()->transform($row[$col],true);
                }

                $r[$c['handle']] = $val;
            }

            $res[] = $r;
        }

        return $res;

    }
}