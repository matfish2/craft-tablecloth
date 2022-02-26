<?php


namespace matfish\Tablecloth\models\Column;


abstract class ProductCategoryColumn extends ProductColumn
{
    protected string $categoryClass;

    public function isList(): bool
    {
        return true;
    }

    public function isSingleList(): bool
    {
        return true;
    }

    public function getList(): array
    {
        $list = [];

        foreach ($this->getOptions() as $option) {
            $list[$option['value']] = $option['label'];
        }

        return $list;
    }

    protected function getOptions(): array
    {
        return array_map(static function ($c) {
            return [
                'label' => $c->name,
                'value' => $c->id
            ];
        }, $this->categoryClass::find()->all());
    }
}