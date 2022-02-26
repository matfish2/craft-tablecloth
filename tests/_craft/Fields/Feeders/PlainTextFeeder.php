<?php

namespace tableclothtests\_craft\Fields\Feeders;

use tableclothtests\_craft\migrations\services\FakerService;

class PlainTextFeeder extends FieldFeeder
{
    public function get($options = null)
    {
        return FakerService::sentence();
    }
}