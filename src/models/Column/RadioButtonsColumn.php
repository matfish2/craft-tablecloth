<?php


namespace matfish\Tablecloth\models\Column;


class RadioButtonsColumn extends ListColumn
{

    protected function getOptions(): array
    {
        return $this->getField()->options;
    }
}