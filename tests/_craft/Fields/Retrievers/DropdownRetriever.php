<?php


namespace tableclothtests\_craft\Fields\Retrievers;


class DropdownRetriever extends FieldValueRetriever
{

    public function transform($value)
    {
        $selected = array_values(array_filter($value->getOptions(), static function ($option) {
            return $option->selected;
        }));

        if (!$selected) {
            return null;
        }

        return [
            'label' => $selected[0]->label,
            'value' => $selected[0]->value
        ];

    }
}