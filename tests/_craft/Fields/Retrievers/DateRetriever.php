<?php


namespace tableclothtests\_craft\Fields\Retrievers;


use craft\helpers\DateTimeHelper;

class DateRetriever extends FieldValueRetriever
{
    public function transform($value , $table=false)
    {
        return is_object($value) ? $value->format('Y-m-d H:i:s') : DateTimeHelper::toDateTime($value)->format('Y-m-d H:i:s');
    }
}