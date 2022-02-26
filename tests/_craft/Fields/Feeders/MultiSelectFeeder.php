<?php


namespace tableclothtests\_craft\Fields\Feeders;


use tableclothtests\_craft\migrations\services\FakerService;

class MultiSelectFeeder extends FieldFeeder
{
    /**
     * @throws \Exception
     */
    public function get($options = null)
    {
        return FakerService::arrayElements($this->getOptions(),random_int(1,3));
    }
}