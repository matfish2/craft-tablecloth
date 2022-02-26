<?php


namespace tableclothtests\_craft\Fields\Retrievers;


class MultiSelectRetriever extends FieldValueRetriever
{

    public function transform($value)
    {
        $selected = array_values(array_filter($value->getOptions(), static function ($option) {
            return $option->selected;
        }));

        return array_map(static function ($item) {
            return [
                'label' => $item->label,
                'value' => $item->value
            ];
        }, $selected);
    }
}