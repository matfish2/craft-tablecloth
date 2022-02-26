<?php


namespace tableclothtests\_craft\Fields\Feeders;


use tableclothtests\_craft\migrations\services\FakerService;

class UrlFeeder extends FieldFeeder
{

    public function get($options = null)
    {
        return FakerService::url();
    }
}