<?php

namespace matfish\Tablecloth\services\elementqueries\Category;

use craft\db\Query;
use matfish\Tablecloth\services\elementqueries\TableclothQuery;
use matfish\Tablecloth\services\elementqueries\TableclothQueryBuilder;

class CategoryQueryBuilder extends TableclothQueryBuilder
{
    protected string $baseTable = 'categories';

    /**
     * @var int
     */
    protected int $groupId;
    protected int $siteId;

    /**
     * CategoryQueryBuilder constructor.
     * @param int $groupId
     * @param int $siteId
     */
    public function __construct(int $groupId, int $siteId)
    {
        $this->groupId = $groupId;
        $this->siteId = $siteId;
    }

    protected function manipulateQuery(TableclothQuery $query): TableclothQuery
    {
        return $query
            ->leftJoin(['structureelements' => '{{%structureelements}}'],
                "([[structureelements.elementId]] = [[subquery.elementsId]]) AND
                      ([[structureelements.structureId]] = [[subquery.structureId]])")
            ->where('[[categories.groupId]]=:groupId')
            ->addParams([
                'groupId' => $this->groupId
            ]);
    }

    protected function manipulateSubquery(Query $query): Query
    {
        return $query
            ->addSelect('structureelements.structureId')
            ->leftJoin(['structureelements' => '{{%structureelements}}'],
                "([[structureelements.elementId]] = [[elements.id]]) AND
                      (EXISTS(SELECT * FROM {{%structures}} WHERE ([[id]] = [[structureelements.structureId]]) AND ([[dateDeleted]] IS NULL)))")
            ->orderBy([
                'structureelements.lft' => SORT_ASC,
                'elements.dateCreated' => SORT_DESC
            ]);
    }
}