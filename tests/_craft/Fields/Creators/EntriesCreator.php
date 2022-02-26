<?php


namespace tableclothtests\_craft\Fields\Creators;


class EntriesCreator extends FieldCreator
{
    public function getFieldData(): array
    {
        return [
            $this->settingsKey => [
                'sources' => '*'
            ]
        ];
    }
}