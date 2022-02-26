<?php


namespace tableclothtests\_craft\Fields\Retrievers;


use craft\elements\Entry;

class ColorRetriever extends FieldValueRetriever
{
    public function transform($value)
    {
        return $value->getHex();
    }
}