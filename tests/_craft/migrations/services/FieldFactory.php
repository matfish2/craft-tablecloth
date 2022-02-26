<?php

namespace tableclothtests\_craft\migrations\services;

use tableclothtests\_craft\Fields\Creators\FieldCreator;
use tableclothtests\_craft\Fields\Feeders\FieldFeeder;
use tableclothtests\_craft\Fields\Retrievers\FieldValueRetriever;

class FieldFactory
{
    protected string $fieldClass;

    public function __construct(string $fieldClass)
    {
        if (!in_array($fieldClass, FieldsList::$list, true)) {
            throw new \Exception("Unknown field class" . $fieldClass);
        }

        $this->fieldClass = $fieldClass;

    }

    public function getCreatorClass($isMatrix = false): FieldCreator
    {
        $data = $this->getFieldData();
        $cls = "tableclothtests\_craft\Fields\Creators\\{$data['shortName']}Creator";

        return new $cls($data, $isMatrix);
    }

    public function getFeederClass($isMatrix = false): FieldFeeder
    {
        $data = $this->getFieldData();
        $cls = "tableclothtests\_craft\Fields\Feeders\\{$data['shortName']}Feeder";

        return new $cls($data, $isMatrix);
    }

    public function getRetrieverClass() : FieldValueRetriever
    {
        $data = $this->getFieldData();
        $cls = "tableclothtests\_craft\Fields\Retrievers\\{$data['shortName']}Retriever";

        return new $cls($data);

    }

    public function getFieldData(): array
    {
        return [
            'type' => $this->fieldClass,
            'shortName' => $this->getClassShortName(),
            'name' => $this->getFieldName(),
            'handle' => $this->getFieldHandle(),
        ];
    }

    private function getClassShortName(): string
    {
        return (new \ReflectionClass($this->fieldClass))->getShortName();
    }

    private function getFieldHandle(): string
    {
        $cls = $this->getClassShortName();
        $cls[0] = strtolower($cls[0]);

        return $cls . 'Test';
    }

    private function getFieldName(): string
    {
        $cls = $this->getClassShortName();
        $pieces = preg_split('/(?=[A-Z])/', $cls);

        return trim(implode(' ', $pieces) . ' Test');
    }

}