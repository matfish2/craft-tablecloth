<?php

namespace matfish\Tablecloth\actions;

use craft\elements\actions\Delete;

class DeleteAction extends Delete
{
    public function canHardDelete(): bool
    {
        return false;
    }
}