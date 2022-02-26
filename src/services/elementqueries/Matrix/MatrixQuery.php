<?php

namespace matfish\Tablecloth\services\elementqueries\Matrix;

use craft\fields\BaseRelationField;
use craft\fields\Matrix;
use matfish\Tablecloth\services\elementqueries\TableclothQuery;

/**
 * The matrix query is a special query class as it queries data for a specific matrix field
 * Data is eager loaded by relevant element (e.g entry) ids and then combined with the main query result
 * Class MatrixQuery
 * @package matfish\Tablecloth\services\elementqueries\Matrix
 */
class MatrixQuery
{
    /**
     * @var TableclothQuery
     */
    protected TableclothQuery $builder;

    /**
     * @var Matrix
     */
    protected Matrix $field;

    /**
     * @var int
     */
    protected int $siteId;

    /**
     * MatrixQuery constructor.
     * @param Matrix $field
     */
    public function __construct(Matrix $field, $siteId)
    {
        $this->field = $field;
        $this->siteId = $siteId;
    }

    /**
     * @param $elements array of element ids (e.g entries) to eager load
     * @return array|\yii\db\DataReader
     * @throws \yii\db\Exception
     */
    public function getData(array $elements)
    {
        $columns = $this->getMatrixColumns();

        $this->builder = (new MatrixQueryBuilder($this->field, $elements))->getBaseQuery($this->siteId);
        $this->builder->setBaseTable('matrixblocks');

        $this->builder
            ->addSelect($columns);

        foreach ($this->relationFields() as $field) {
            $this->builder->joinRelationIds($field->id, $this->relationTable($field));
        }

        return $this->builder->get();
    }

    protected function relationTable($field) {
        $cls = get_class($field);
        $ps = explode('\\', $cls);
        $sn = array_pop($ps);
        return strtolower($sn);
    }

    private function relationFields()
    {
        return array_filter($this->field->getBlockTypeFields(), static function ($field) {
            return $field instanceof BaseRelationField;
        });
    }

    /**
     * @return array
     */
    private function getMatrixColumns(): array
    {
        $columns = [];

        $relationFields = $this->relationFields();

        foreach ($relationFields as $field) {
            $rel = $this->relationTable($field);
            $columns[] = "[[{$rel}_{$field->id}.{$rel}]] [[{$field->columnPrefix}{$field->handle}]]";
        }

        $fields = array_filter($this->field->getBlockTypeFields(), static function ($field) {
            return !$field instanceof BaseRelationField;
        });


        foreach ($fields as $field) {
            $column = $field->columnPrefix . $field->handle;
            $handle = $column;
            if ($field->columnSuffix) {
                $column .= '_' . $field->columnSuffix;
            }

            $columns[] = "[[$column]] [[$handle]]";
        }

        return $columns;
    }
}