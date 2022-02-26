<?php


namespace matfish\Tablecloth\models\Column;


abstract class ListColumn extends Column
{
    protected string $label = 'label';

    public function getList(): array
    {
        $list = [];

        foreach ($this->getOptions() as $option) {
            $list[$option['value']] = $option[$this->label];
        }

        return $list;
    }

    abstract protected function getOptions(): array;
}