<?php

namespace matfish\Tablecloth\services\elementqueries\Tag;


use matfish\Tablecloth\services\elementqueries\BaseElementQuery;
use matfish\Tablecloth\services\elementqueries\BaseSourceQueryBuilder;
use matfish\Tablecloth\services\elementqueries\TableclothQuery;

class TagQuery extends BaseElementQuery
{

    protected function getBuilder(): TableclothQuery
    {
        return (new TagQueryBuilder($this->dataTable))->getBaseQuery($this->siteId);
    }

    protected function getDefaultSort(): string
    {
        return 'tags.dateCreated';
    }

    protected function getTableName(): string
    {
        return 'tags';
    }
}