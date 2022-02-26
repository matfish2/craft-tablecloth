<?php

namespace matfish\Tablecloth\models\Column;

abstract class RelationsColumn extends ListColumn
{
    protected string $label = 'data';

    public function getDbColumn(string $context = Column::CONTEXT_SELECT): string
    {
        $relation = strtolower($this->fieldType);

        $handle = "{$this->getRelationPrefix()}{$relation}_{$this->fieldId}.{$relation}";

        if ($context === Column::CONTEXT_SELECT) {
            $handle .= " [[{$this->getFrontEndHandle()}]]";
        }

        return $handle;
    }

    public function getRelationPrefix(): string
    {
        return $this->isProductVariantCustomField() ? 'variant_' : '';
    }
}