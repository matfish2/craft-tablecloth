<?php


namespace tableclothtests\_craft\Fields\Feeders;


use tableclothtests\_craft\migrations\services\FakerService;

class CheckboxesFeeder extends FieldFeeder
{

    public function get($options = null)
    {
        return FakerService::arrayElements($this->getOptions(),random_int(1,3));
    }
}