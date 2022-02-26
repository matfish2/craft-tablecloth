<?php

namespace tableclothtests\_craft\Fields\Retrievers;


use craft\base\ElementInterface;
use craft\elements\Entry;

abstract class FieldValueRetriever
{
    protected array $data;

    /**
     * FieldValueRetriever constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @throws \craft\errors\InvalidFieldException
     */
    public function get(ElementInterface $element) {
        $value = $element->getFieldValue($this->data['handle']);

        $nullValue = in_array(get_called_class(),[
           TableRetriever::class,
           MultiSelectRetriever::class,
           MatrixRetriever::class,
           CheckboxesRetriever::class
        ]) ? [] : null;

        return $value ? $this->transform($value) : $nullValue;
    }

    abstract public function transform($value);

}