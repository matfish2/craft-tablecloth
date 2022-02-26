<?php


namespace tableclothtests\_craft\Fields\Feeders;


use tableclothtests\_craft\migrations\services\FakerService;

class NumberFeeder extends FieldFeeder
{

    public function get($options = null)
    {
        return FakerService::number();
    }
}