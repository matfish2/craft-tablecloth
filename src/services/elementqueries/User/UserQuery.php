<?php

namespace matfish\Tablecloth\services\elementqueries\User;

use matfish\Tablecloth\services\elementqueries\BaseElementQuery;
use matfish\Tablecloth\services\elementqueries\BaseSourceQueryBuilder;
use matfish\Tablecloth\services\elementqueries\TableclothQuery;

class UserQuery extends BaseElementQuery
{
    protected function getBuilder(): TableclothQuery
    {
        return (new UserQueryBuilder($this->dataTable))->getBaseQuery($this->siteId);
    }

    protected function getDefaultSort(): string
    {
        return 'users.dateCreated';
    }

    protected function getTableName(): string
    {
        return 'users';
    }
}