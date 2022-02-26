<?php


namespace tableclothtests\_craft\Fields\Creators;


class CheckboxesCreator extends FieldCreator
{
 protected function getFieldData(): array
    {
        $options = [];

        for ($i = 1; $i <= 5; $i++) {
            $options[] = [
                'label' => 'Checkbox ' . $i,
                'value' => 'checkbox' . $i
            ];
        }

        return [$this->settingsKey => ['options' => $options]];
    }
}