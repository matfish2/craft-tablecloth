<?php


namespace tableclothtests\_craft\Fields\Creators;


class DropdownCreator extends FieldCreator
{
    protected function getFieldData(): array
    {
        $options = [];

        for ($i = 1; $i <= 5; $i++) {
            $options[] = [
                'label' => 'Option ' . $i,
                'value' => 'option' . $i
            ];
        }

        return [$this->settingsKey => ['options' => $options]];
    }
}