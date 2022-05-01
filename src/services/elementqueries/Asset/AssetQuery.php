<?php

namespace matfish\Tablecloth\services\elementqueries\Asset;

use matfish\Tablecloth\services\elementqueries\BaseElementQuery;
use matfish\Tablecloth\services\elementqueries\TableclothQuery;

class AssetQuery extends BaseElementQuery
{
    protected function getBuilder(): TableclothQuery
    {
        return (new AssetQueryBuilder($this->dataTable))->getBaseQuery($this->siteId);
    }

    protected function getDefaultSort(): string
    {
        return 'assets.dateCreated';
    }

    protected function getTableName(): string
    {
        return 'assets';
    }
}