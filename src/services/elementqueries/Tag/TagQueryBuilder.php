<?php


namespace matfish\Tablecloth\services\elementqueries\Tag;


use craft\db\Query;
use matfish\Tablecloth\services\elementqueries\BaseSourceQueryBuilder;
use matfish\Tablecloth\services\elementqueries\TableclothQuery;
use matfish\Tablecloth\services\elementqueries\TableclothQueryBuilder;

class TagQueryBuilder extends TableclothQueryBuilder
{
    protected string $baseTable = 'tags';

    protected function manipulateQuery(TableclothQuery $query): TableclothQuery
    {
        return $query->andWhere('[[tags.groupId]]=:groupId')
            ->addParams([
                'groupId' => $this->dataTable->groupId
            ]);
    }

    protected function manipulateSubquery(Query $query): Query
    {
        return $query;
    }
}