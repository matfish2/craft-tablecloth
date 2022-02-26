<?php


namespace matfish\Tablecloth\models\Column;


class CheckboxesColumn extends ListColumn
{
    protected function getOptions(): array
    {
        return $this->getField()->options;
    }
}