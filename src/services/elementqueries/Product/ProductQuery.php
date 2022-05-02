<?php

namespace matfish\Tablecloth\services\elementqueries\Product;

use matfish\Tablecloth\services\elementqueries\BaseElementQuery;
use matfish\Tablecloth\services\elementqueries\TableclothQuery;

class ProductQuery extends BaseElementQuery
{
    protected function excludeVariantFields(): bool {
        return $this->dataTable->hasVariants() && $this->dataTable->variantsStrategy==='nest';
    }

    protected function getBuilder(): TableclothQuery
    {
        return (new ProductQueryBuilder($this->dataTable))->getBaseQuery($this->siteId);
    }

    protected function getDefaultSort(): string
    {
        return 'products.dateCreated';
    }

    protected function getTableName(): string
    {
        return $this->dataTable->hasVariants() && $this->dataTable->variantsStrategy==='join' ? 'variants' : 'products';
    }

    protected function transformHandle($handle)
    {
       return str_replace('variant__','variant:', $handle);
    }
}