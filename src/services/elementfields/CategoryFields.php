<?php


namespace matfish\Tablecloth\services\elementfields;

use craft\base\ElementInterface;
use craft\elements\Category;
use matfish\Tablecloth\services\elementfields\traits\CustomFieldsTrait;

class CategoryFields extends BaseElementFields
{
    use CustomFieldsTrait;

    protected function getElement(): ElementInterface
    {
        return new Category($this->qualifiers);
    }

    protected function nativeFields(): array
    {
        return [
        ];
    }

    protected function customFields(): array
    {
        return $this->getCustomFieldsForElement($this->getElement());
    }
}