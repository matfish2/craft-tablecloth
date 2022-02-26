<?php


namespace tableclothtests\_craft\Fields\Feeders;


use tableclothtests\_craft\migrations\services\FakerService;

class TimeFeeder extends FieldFeeder
{

    /**
     * @throws \Exception
     */
    public function get($options = null)
    {
        return FakerService::time();
    }
}