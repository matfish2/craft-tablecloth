<?php

namespace matfish\Tablecloth\models;

use craft\base\Model;

class Settings extends Model
{
    use OverrideableSettings;

    public function getTableOption($option)
    {
        $method = "get" . ucfirst($option);

        return method_exists($this, $method) ? $this->{$method}() : $this->{$option};
    }
}