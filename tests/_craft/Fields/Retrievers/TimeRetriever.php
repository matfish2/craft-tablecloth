<?php


namespace tableclothtests\_craft\Fields\Retrievers;


class TimeRetriever extends FieldValueRetriever
{

    public function transform($value)
    {
        return $value->format('H:i');
    }
}