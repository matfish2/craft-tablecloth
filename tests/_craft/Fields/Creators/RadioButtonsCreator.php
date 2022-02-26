<?php


namespace tableclothtests\_craft\Fields\Creators;


class RadioButtonsCreator extends FieldCreator
{
    protected function getFieldData(): array
    {
        $options = [];

        for ($i = 1; $i <= 5; $i++) {
            $options[] = [
                'label' => 'Radio ' . $i,
                'value' => 'radio' . $i
            ];
        }

        return [$this->settingsKey => ['options' => $options]];
    }
}