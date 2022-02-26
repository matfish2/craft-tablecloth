<?php


namespace matfish\Tablecloth\models\Column;


class ProductColumn extends Column
{
    public function getDbColumn(string $context = self::CONTEXT_SELECT): string
    {
        return "products.{$this->getFrontEndHandle()}";
    }

    public function getFrontEndHandle() : string {
        return explode(':', $this->handle)[1];
    }

    public function isProduct(): bool
    {
        return true;
    }
}