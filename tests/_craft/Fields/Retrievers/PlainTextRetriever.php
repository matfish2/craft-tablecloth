<?php


namespace tableclothtests\_craft\Fields\Retrievers;


class PlainTextRetriever extends FieldValueRetriever
{

    public function transform($value)
    {
        return $value;
    }
}