<?php

namespace matfish\Tablecloth\services\elementqueries\Category;

use craft\db\Query;
use matfish\Tablecloth\services\elementqueries\TableclothQuery;
use matfish\Tablecloth\services\elementqueries\TableclothQueryBuilder;

class CategoryQueryBuilder extends TableclothQueryBuilder
{
    protected string $baseTable = 'categories';

    protected function manipulateQuery(TableclothQuery $query): TableclothQuery
    {
        return $query
            ->where('[[categories.groupId]]=:groupId')
            ->addParams([
                'groupId' => $this->dataTable->groupId
            ]);
    }

    protected function manipulateSubquery(Query $query): Query
    {
        return $query;
    }
}