<?php


namespace matfish\Tablecloth\models\Column;


class DropdownColumn extends ListColumn
{

    protected function getOptions(): array
    {
        return $this->getField()->options;
    }
}