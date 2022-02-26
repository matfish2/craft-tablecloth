<?php

namespace matfish\Tablecloth\services\elementqueries\Entry;

use craft\db\Query;
use matfish\Tablecloth\services\elementqueries\TableclothQuery;
use matfish\Tablecloth\services\elementqueries\TableclothQueryBuilder;

class EntryQueryBuilder extends TableclothQueryBuilder
{
    protected string $baseTable = 'entries';

    protected function manipulateQuery(TableclothQuery $query): TableclothQuery
    {
        return $query->andWhere('[[entries.postDate]]<=NOW()')
            ->andWhere('[[entries.expiryDate]] IS NULL OR [[entries.expiryDate]]> NOW()')
            ->andWhere('[[entries.sectionId]]=:sectionId AND [[entries.typeId]]=:typeId')
            ->addParams([
                'sectionId' => $this->dataTable->sectionId,
                'typeId' => $this->dataTable->typeId
            ]);
    }

    protected function manipulateSubquery(Query $query): Query
    {
        return $query;
    }
}