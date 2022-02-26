<?php


namespace tableclothtests\_craft\Fields\Creators;

use tableclothtests\_craft\migrations\services\FieldsList;

class TableCreator extends FieldCreator
{
    /**
     * @throws \Exception
     */
    public function getFieldData(): array
    {
        $res = [];

        foreach (FieldsList::$tableFields as $index => $type) {
            $colId = $index + 1;
            $res["col{$colId}"] = [
                'type'=>$type,
                'heading'=>ucfirst($type)  . ' Test',
                'handle'=>$type.'Test'
            ];

            if ($type==='select') {
                $options = [];

                for ($i=1;$i<=5;$i++) {
                    $options[] = [
                      'label'=>'Table Dropdown ' . $i,
                      'value'=>'tableDropdown_' . $i
                    ];
                }
                $res["col{$colId}"]['options'] = $options;
            }
        }

        return [
           $this->settingsKey => [
                'columns' => $res
            ]
        ];
    }
}