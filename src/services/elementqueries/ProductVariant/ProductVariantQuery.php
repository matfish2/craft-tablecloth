<?php

namespace matfish\Tablecloth\services\elementqueries\ProductVariant;

use matfish\Tablecloth\collections\ColumnsCollection;
use matfish\Tablecloth\elements\DataTable;
use matfish\Tablecloth\models\Column\ColumnInterface;
use matfish\Tablecloth\services\elementqueries\Matrix\LoadsMatrices;
use matfish\Tablecloth\services\elementqueries\TableclothQuery;
use matfish\Tablecloth\services\normalizers\Normalizer;

/**
 * The product variation query is a special query class as it queries data for a product type with multiple variants
 * when the nested (as opposed to join) strategy was selected
 * Data is eager loaded by product ids and then combined with the main query result
 * Class ProductVariationQuery
 * @package matfish\Tablecloth\services\elementqueries\ProductVariation
 */
class ProductVariantQuery
{
    use LoadsMatrices;

    /**
     * @var TableclothQuery
     */
    protected TableclothQuery $builder;
    protected DataTable $dataTable;

    /**
     * ProductVariationQuery constructor.
     * @param DataTable $dataTable
     */
    public function __construct(DataTable $dataTable)
    {
        $this->dataTable = $dataTable;
        $this->builder = (new TableclothQuery())->setBaseTable('variants');
    }


    /**
     * @param $elements array of products ids to eager load
     * @return array|\yii\db\DataReader
     * @throws \yii\db\Exception
     * @throws \matfish\Tablecloth\exceptions\TableclothException
     */
    public function getData(array $products)
    {
        $columns = $this->getVariantColumns();
        $dbColumns = $columns->map(function (ColumnInterface $column) {
            return $column->getDbColumn();
        })->all();

        $dbColumns = array_merge(['variants.id', 'variants.dateCreated', 'variants.dateUpdated'], $dbColumns);

        $this->builder = (new ProductVariantQueryBuilder($products))->getBaseQuery();

        $this->builder
            ->addSelect($dbColumns);

        $columns->categoryColumns()->each(function (ColumnInterface $column) {
            $this->builder->joinCategories($column->getField()->id, true);
        });

        $columns->tagColumns()->each(function (ColumnInterface $column) {
            $this->builder->joinTags($column->getField()->id, true);
        });

        $columns->entriesColumns()->each(function (ColumnInterface $column) {
            $this->builder->joinEntries($column->getField()->id, true);
        });

        $columns->usersColumns()->each(function (ColumnInterface $column) {
            $this->builder->joinUsers($column->getField()->id, true);
        });

        $columns->assetsColumns()->each(function (ColumnInterface $column) {
            $this->builder->joinAssets($column->getField()->id, true);
        });

        $collection = $this->dataTable->getColumnsCollection()
            ->customColumns()
            ->variants()
            ->notText();

        $data = $this->builder->get();

        $data = $this->attachMatrices($data);

        return (new Normalizer($this->dataTable, $data))->normalize($collection)->toArray();
    }

    /**
     * @throws \matfish\Tablecloth\exceptions\TableclothException
     * @throws \JsonException
     */
    private function getVariantColumns(): ColumnsCollection
    {
        return $this->dataTable->getColumnsCollection()->variants()->notMatrixColumns();
    }
}