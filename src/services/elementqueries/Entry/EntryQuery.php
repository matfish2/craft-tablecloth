<?php

namespace matfish\Tablecloth\services\elementqueries\Entry;

use matfish\Tablecloth\services\elementqueries\BaseElementQuery;
use matfish\Tablecloth\services\elementqueries\TableclothQuery;

class EntryQuery extends BaseElementQuery
{

    protected function getBuilder(): TableclothQuery
    {
        return (new EntryQueryBuilder($this->dataTable))->getBaseQuery($this->siteId);
    }

    protected function getDefaultSort(): string
    {
        return 'entries.dateCreated';
    }

    protected function getTableName(): string
    {
        return 'entries';
    }
}