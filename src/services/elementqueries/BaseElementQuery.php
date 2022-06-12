<?php


namespace matfish\Tablecloth\services\elementqueries;

use Craft;
use craft\db\Table;
use matfish\Tablecloth\enums\DataTypes;
use matfish\Tablecloth\models\Column\Column;
use matfish\Tablecloth\models\Column\ColumnInterface;
use matfish\Tablecloth\services\dataset\PrefilterSqlGenerator;
use matfish\Tablecloth\services\elementqueries\Matrix\LoadsMatrices;
use matfish\Tablecloth\services\elementqueries\ProductVariant\ProductVariantCombiner;
use matfish\Tablecloth\services\elementqueries\ProductVariant\ProductVariantQuery;
use matfish\Tablecloth\services\normalizers\Normalizer;

abstract class BaseElementQuery extends BaseSourceQuery
{
    use LoadsMatrices;

    protected string $defaultSort;
    protected array $params = [];

    /**
     * @throws \yii\db\Exception|\JsonException
     */
    public function getInitialData(): Normalizer
    {
        $this->_prepareQuery();

        if ($this->dataTable->initialSortColumn) {
            $initialOrderColumn = $this->dataTable->getColumnByHandle($this->dataTable->initialSortColumn)->getDbColumn(false);
            $initialOrderAsc = $this->dataTable->initialSortColumn ? $this->dataTable->initialSortAsc : false;
            $this->builder->orderBy([$initialOrderColumn => $initialOrderAsc ? SORT_ASC : SORT_DESC]);
        } else {
            $this->builder->orderBy([
                $this->getDefaultSort() => SORT_DESC,
                'elements.id' => SORT_ASC
            ]);
        }

        $data = $this->eagerLoadRelations();

        return new Normalizer($this->dataTable, $data);
    }

    protected function _prepareQuery(): void
    {
        $this->builder = $this->getBuilder();

        $this->builder->select($this->getColumns());
        $this->applyJoins();
        $this->applyPrefilter();

        // Filtering
        // Free search
        $this->applySearch();
        // Custom Filters
        $this->applyFilters();

        // For server table only get the first page on initial request
        if ($this->dataTable->serverTable) {
            $perPage = $this->getPerPage();
            $this->builder->limit($perPage)->offset(0);
        }
    }

    /**
     * @return Normalizer
     * @throws \yii\db\Exception
     * @throws \JsonException
     */
    public function getData(array $params = []): Normalizer
    {
        $this->params = $params;

        $this->_prepareQuery();

        // Pagination
        $perPage = $this->getPerPage();
        $page = $this->getParam('p') ?? 1;

        $this->builder->limit($perPage)->offset($perPage * ($page - 1));

        // Sorting
        $sortColumn = $this->transformHandle($this->getParam('sortColumn'));
        $sortDirection = $this->getParam('sortDirection');

        if ($sortColumn) {
            $column = $this->dataTable->getColumnByHandle($sortColumn);
//            if ($column->isCustomList()) {
//                $this->builder->addList($column);
//                $this->builder->leftJoin("list_$sortColumn", $column->getDbColumn(Column::CONTEXT_JOIN), 'value', 'content', true);
//            }
            $this->builder->orderBy([$column->getDbColumn(Column::CONTEXT_SORT) => $sortDirection === 'ASC' ? SORT_ASC : SORT_DESC]);

        } else {
            $this->builder->orderBy([$this->getDefaultSort() => SORT_DESC,"[[{$this->getTableName()}.id]]"=>SORT_ASC]);
        }

        $data = $this->eagerLoadRelations();

        return new Normalizer($this->dataTable, $data);
    }

    /**
     * @param $handle
     * @return mixed
     */
    protected function transformHandle($handle) {
        return $handle;
    }

    /**
     * Get total count
     * Only relevant for server tables where an independent request is sent to get total count
     * @return int
     * @throws \yii\db\Exception
     */
    public function getCount($params = []): int
    {
        $this->params = $params;

        $this->builder = $this->getBuilder();

        $this->applyJoins();
        $this->applySearch();
        $this->applyFilters();
        $this->applyPrefilter();

        return $this->builder->count();
    }

    /**
     * @return array
     */
    public function getColumns(): array
    {
        // matrix fields are eager loaded separately
        $columns = $this->dataTable->getColumnsCollection()->usedColumns($this->dataTable);

        if ($this->excludeVariantFields()) {
            $columns = $columns->notVariants();
        }

        $tableName = $this->getTableName();

        return array_merge(["{$tableName}.id"], $columns->dbColumns());
    }


    abstract protected function getBuilder(): TableclothQuery;

    abstract protected function getDefaultSort(): string;

    abstract protected function getTableName(): string;

    /**
     * @param $param
     * @return array|mixed|string
     */
    private function getParam($param)
    {
        return $this->params[$param] ?? null;
    }

    /**
     * @throws \matfish\Tablecloth\exceptions\TableclothException
     */
    private function applyFilters()
    {
        $qs = $this->getParam('filters');

        if (!$qs) {
            return;
        }

        $res = [];
        $params = [];

        $collection = $this->dataTable->getColumnsCollection();

        foreach ($qs as $handle => $value) {
            $ps = explode('_*_', $handle);
            $colHandle = $ps[0];


            $col = $collection->find($colHandle);
            $dbCol = $col->getDbColumn(Column::CONTEXT_FILTER);
            $dataType = $col->dataType;

            if (is_array($value)) {
                $res[] = "{$dbCol} BETWEEN :{$colHandle}_min AND :{$colHandle}_max";
                $params["{$colHandle}_min"] = $value[0];
                $params["{$colHandle}_max"] = $value[1];
                continue;
            }


            if ($dataType === DataTypes::Text) {
                $operand = $ps[1] ?? "like";
                $res[] = "{$dbCol} {$operand} :{$colHandle}";
                $value = "%{$value}%";
            } elseif ($col->multiple) {
                $dbCol = "[[searchindex_{$col->getFrontEndHandle()}]].[[keywords]]";
                $this->builder->addMultipleList($col);
                $res[] = "{$dbCol} like :{$colHandle}";
                $value = "%{$value}%";
            } else {
                if ($col->dataType === DataTypes::Boolean) {
                    $value = is_bool($value) ? $value : $value === 'true' || $value === '1';
                }

                $operand = $ps[1] ?? '=';

                $res[] = "{$dbCol}{$operand}:{$colHandle}";
            }

            $params[$colHandle] = $value;
        }

        $sql = implode(' AND ', $res);

        $this->builder->andWhere($sql)
            ->addParams($params);
    }

    private function applySearch(): void
    {
        $q = $this->getParam('q');

        if (!$q) {
            return;
        }

        $res = [];

        $filterableColumns = $this->dataTable
            ->getColumnsCollection()
            ->filterable()
            ->all();

        foreach ($filterableColumns as $column) {
            $handle = $column->getFrontEndHandle();

            if ($column->isSingleCustomList()) {
                $dbCol = "[[list_{$handle}]].[[label]]";
                $this->builder->addSingleCustomList($column);
            } elseif ($column->multiple) {
                $dbCol = "[[searchindex_{$handle}]].[[keywords]]";
                $this->builder->addMultipleList($column);
            } else {
                $dbCol = $column->getDbColumn(Column::CONTEXT_FILTER);
            }

            $res[] = $dbCol;
        }

        $isPostgres = Craft::$app->db->driverName === 'pgsql';
        $sql = array_map(static function ($dbCol) use ($q, $isPostgres) {
            $col = $isPostgres ? "CAST($dbCol as TEXT)" : $dbCol;
            return "$col LIKE '%$q%'";
        }, $res);

        $this->builder->andWhere(implode(' OR ', $sql));
    }

    private function applyJoins(): void
    {
        $usedColumns = $this->dataTable
            ->getColumnsCollection()
            ->usedColumns($this->dataTable, true);

        if ($this->excludeVariantFields()) {
            $usedColumns->notVariants();
        }

        $handles = $usedColumns->listColumns()->handles()->all();

        if (in_array('authorId', $handles, true)) {
            $this->builder->leftJoin(['users' => Table::USERS], '[[entries.authorId]]=[[users.id]]');
        }

        $usedColumns->categoryColumns()->each(function (ColumnInterface $column) {
            $this->builder->joinCategories($column->getField()->id, $column->isProductVariant());
        });

        $usedColumns->tagColumns()->each(function (ColumnInterface $column) {
            $this->builder->joinTags($column->getField()->id, $column->isProductVariant());
        });

        $usedColumns->entriesColumns()->each(function (ColumnInterface $column) {
            $this->builder->joinEntries($column->getField()->id, $column->isProductVariant());
        });

        $usedColumns->usersColumns()->each(function (ColumnInterface $column) {
            $this->builder->joinUsers($column->getField()->id, $column->isProductVariant());
        });

        $usedColumns->assetsColumns()->each(function (ColumnInterface $column) {
            $this->builder->joinAssets($column->getField()->id, $column->isProductVariant());
        });
    }

    /**
     * @return int
     */
    protected function getPerPage(): int
    {
        return $this->getParam('perPage') ?? $this->dataTable->getTableOption('initialPerPage');
    }

    /**
     * @throws \matfish\Tablecloth\exceptions\TableclothException
     */
    private function applyPrefilter(): void
    {
        if ($this->dataTable->datasetPrefilter) {
            $this->builder->andWhere((new PrefilterSqlGenerator($this->dataTable))->get());
        }
    }

    /**
     * @return bool
     */
    protected function excludeVariantFields(): bool
    {
        return false;
    }

    private function attachVariants(array $data): array
    {
        $dataIds = $this->getDataIds($data);
        $variantsData = (new ProductVariantQuery($this->dataTable))->getData($dataIds);

        return (new ProductVariantCombiner())->combine($data, $variantsData);
    }

    /**
     * @return array|\yii\db\DataReader
     * @throws \JsonException
     * @throws \yii\db\Exception
     */
    protected function eagerLoadRelations(): array
    {
        $data = $this->builder->get();

        if ($this->dataTable->enableChildRows && $this->dataTable->childRowMatrixFields && count($data) > 0) {
            $data = $this->attachMatrices($data);
        }

        if ($this->dataTable->variantsStrategy === 'nest' && $this->dataTable->hasVariants() && count($data) > 0) {
            $data = $this->attachVariants($data);
        }

        return $data;
    }
}