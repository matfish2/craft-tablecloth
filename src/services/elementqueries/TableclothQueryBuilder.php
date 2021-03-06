<?php


namespace matfish\Tablecloth\services\elementqueries;

use Craft;
use craft\db\Query;
use craft\db\Table;
use matfish\Tablecloth\elements\DataTable;

abstract class TableclothQueryBuilder
{
    protected DataTable $dataTable;
    protected string $baseTable;

    protected array $map = [
        'elements' => Table::ELEMENTS,
        'assets' => Table::ASSETS,
        'content' => Table::CONTENT,
        'volume_folders' => Table::VOLUMEFOLDERS,
        'elements_sites' => Table::ELEMENTS_SITES,
        'tags' => Table::TAGS,
        'entries' => Table::ENTRIES,
        'categories' => Table::CATEGORIES,
        'users' => Table::USERS,
        'products' => '{{%commerce_products}}',
        'variants' => '{{%commerce_variants}}'
    ];

    /**
     * TableclothQueryBuilder constructor.
     * @param DataTable $dataTable
     */
    public function __construct(DataTable $dataTable)
    {
        $this->dataTable = $dataTable;
    }

    public function getBaseQuery($siteId): TableclothQuery
    {
        $subQuery = (new Query())
            ->addSelect([
                'elementsId' => 'elements.id',
                'elementsSitesId' => 'elements_sites.id',
                'contentId' => 'content.id'
            ])
            ->from($this->aliasedTable('elements'))
            ->innerJoin($this->aliasedTable($this->baseTable), "[[$this->baseTable.id]] = [[elements.id]]")
            ->innerJoin($this->aliasedTable('elements_sites'), '[[elements_sites.elementId]] = [[elements.id]]')
            ->innerJoin($this->aliasedTable('content'), '[[content.elementId]] = [[elements.id]] AND [[content.siteId]] = [[elements_sites.siteId]]')
            ->where([
                'elements.archived' => false,
                'elements.enabled' => true,
                'elements.dateDeleted' => null,
                'elements.draftId' => null,
                'elements.revisionId' => null
            ]);

        $subQuery = $this->manipulateSubquery($subQuery);

        if (Craft::$app->getIsMultiSite(false, true)) {
            $subQuery->andWhere(['elements_sites.siteId' => $siteId]);
        }

        $query = (new TableclothQuery())
            ->setBaseTable($this->baseTable)
            ->from(['subquery' => $subQuery])
            ->innerJoin($this->aliasedTable('elements'), '[[elements.id]] = [[subquery.elementsId]]')
            ->innerJoin($this->aliasedTable('elements_sites'), '[[elements_sites.id]] = [[subquery.elementsSitesId]]')
            ->innerJoin($this->aliasedTable('content'), '[[content.id]] = [[subquery.contentId]]')
            ->innerJoin($this->aliasedTable($this->baseTable), "[[{$this->baseTable}.id]] = [[subquery.elementsId]]");

        return $this->manipulateQuery($query);
    }

    abstract protected function manipulateQuery(TableclothQuery $query): TableclothQuery;

    abstract protected function manipulateSubquery(Query $query): Query;

    protected function aliasedTable($table)
    {
        return [
            $table => $this->map[$table]
        ];
    }
}