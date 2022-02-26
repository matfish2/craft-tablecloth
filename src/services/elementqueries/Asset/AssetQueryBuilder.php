<?php

namespace matfish\Tablecloth\services\elementqueries\Asset;

use craft\db\Query;
use matfish\Tablecloth\services\elementqueries\TableclothQuery;
use matfish\Tablecloth\services\elementqueries\TableclothQueryBuilder;

class AssetQueryBuilder extends TableclothQueryBuilder
{
    protected string $baseTable = 'assets';

    protected function manipulateSubquery(Query $query): Query
    {
        return $query
            ->innerJoin($this->aliasedTable('volume_folders'), '[[volume_folders.id]] = [[assets.folderId]]');
    }

    protected function manipulateQuery(TableclothQuery $query): TableclothQuery
    {
        return $query->innerJoin($this->aliasedTable('volume_folders'), '[[volume_folders.id]] = [[assets.folderId]]')
            ->where('[[assets.volumeId]]=:volumeId')
            ->addParams([
                'volumeId' => $this->dataTable->groupId,
            ]);
    }
}