<?php


namespace matfish\Tablecloth\models\Column;


class MultiselectColumn extends ListColumn
{

    protected function getOptions(): array
    {
        return $this->getField()->options;
    }
}