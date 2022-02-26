<?php


namespace matfish\Tablecloth\services\elementqueries\User;

use craft\db\Query;
use matfish\Tablecloth\services\elementqueries\TableclothQuery;
use matfish\Tablecloth\services\elementqueries\TableclothQueryBuilder;

class UserQueryBuilder extends TableclothQueryBuilder
{
    protected string $baseTable = 'users';

    protected function manipulateQuery(TableclothQuery $query): TableclothQuery
    {
        $userGroups = $this->dataTable->getUserGroups();

        if (count($userGroups) > 0) {
            $groups = implode(",", $userGroups);
            $query->where("(SELECT count(*) FROM {{%usergroups_users}} [[ug]] WHERE [[ug.userId]]=[[users.id]] AND [[ug.groupId]] in ($groups))>0");
        }

        return $query;
    }

    protected function manipulateSubquery(Query $query): Query
    {
        return $query->andWhere(['users.suspended' => false, 'users.pending' => false]);
    }
}