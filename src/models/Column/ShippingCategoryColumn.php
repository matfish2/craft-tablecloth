<?php


namespace matfish\Tablecloth\models\Column;


use craft\commerce\records\ShippingCategory;

class ShippingCategoryColumn extends ProductCategoryColumn
{
    protected string $categoryClass = ShippingCategory::class;
}