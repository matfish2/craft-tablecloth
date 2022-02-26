<?php

namespace tableclothtests\_craft\Fields\Creators;

abstract class FieldCreator
{
    protected array $data;
    protected bool $isMatrix;
    protected string $settingsKey;
    /**
     * FieldCreator constructor.
     * @param array $data
     */
    public function __construct(array $data, $isMatrix = false)
    {
        $this->data = $data;
        $this->isMatrix = $isMatrix;
        $this->settingsKey = $isMatrix ? 'typesettings' : 'settings';
    }

    public function getData() : array {
        $name = $this->isMatrix ? 'Matrix ' . $this->data['name'] : $this->data['name'];
        $handle = $this->isMatrix ? 'matrix' . $this->data['handle'] : $this->data['handle'];

        return array_merge([
            'name'=>$name,
            'handle'=>$handle,
            'type'=>$this->data['type']
        ], $this->getFieldData());
    }

    protected function getFieldData() : array {
        return [];
    }
}