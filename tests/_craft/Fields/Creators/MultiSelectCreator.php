<?php


namespace tableclothtests\_craft\Fields\Creators;


class MultiSelectCreator extends FieldCreator
{
    protected function getFieldData(): array
    {
        $options = [];

        for ($i = 1; $i <= 5; $i++) {
            $options[] = [
                'label' => 'Multi ' . $i,
                'value' => 'multi' . $i
            ];
        }

        return [$this->settingsKey => ['options' => $options]];
    }
}