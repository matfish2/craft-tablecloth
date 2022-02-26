<?php


namespace matfish\Tablecloth\models\Column;


use craft\commerce\records\TaxCategory;

class TaxCategoryColumn extends ProductCategoryColumn
{
    protected string $categoryClass = TaxCategory::class;
}