<?php


namespace tableclothtests\_craft\Fields\Creators;


use craft\fields\Matrix;
use craft\fields\PlainText;
use tableclothtests\_craft\migrations\services\FieldFactory;
use tableclothtests\_craft\migrations\services\FieldsList;

class MatrixCreator extends FieldCreator
{
    /**
     * @throws \Exception
     */
    public function getFieldData(): array
    {
        $list = array_filter(FieldsList::$list, static function ($item) {
            return $item !== Matrix::class;
        });

        $list = array_chunk($list, 5);

        $res = [];

        foreach ($list as $index => $block) {

            $fields = [];

            foreach ($block as $i=>$fieldClass) {
                $fields["new" . ($i+1)] = (new FieldFactory($fieldClass))->getCreatorClass(true)->getData();
            }

            $res["new" . ($index+1)] = [
                'name' => 'Block ' . ($index + 1),
                'handle' => 'block' . ($index + 1),
                'fields'=>$fields
            ];
        }

        return [
            'settings' => [
                'blockTypes' => $res
            ]
        ];
    }

}