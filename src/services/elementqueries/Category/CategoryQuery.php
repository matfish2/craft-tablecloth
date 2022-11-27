<?php

namespace matfish\Tablecloth\services\elementqueries\Category;

use matfish\Tablecloth\services\elementqueries\BaseElementQuery;
use matfish\Tablecloth\services\elementqueries\TableclothQuery;

class CategoryQuery extends BaseElementQuery
{

    protected function getBuilder(): TableclothQuery
    {
        return (new CategoryQueryBuilder($this->dataTable))->getBaseQuery($this->siteId);
    }

    protected function getDefaultSort(): string
    {
        return 'categories.dateCreated';
    }

    protected function getTableName(): string
    {
        return 'categories';
    }
}