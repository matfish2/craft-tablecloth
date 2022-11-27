<?php

namespace matfish\Tablecloth\services\normalizers;

use matfish\Tablecloth\collections\ColumnsCollection;
use matfish\Tablecloth\elements\DataTable;
use matfish\Tablecloth\enums\DataTypes;
use matfish\Tablecloth\enums\Fields;
use matfish\Tablecloth\exceptions\TableclothException;
use matfish\Tablecloth\models\Column\AuthorColumn;
use matfish\Tablecloth\models\Column\ColumnInterface;

/**
 * Normalize values returned from DB before sending to the client
 * Class Normalizer
 * @package matfish\Tablecloth\services\normalizers
 */
class Normalizer
{
    protected DataTable $datatable;
    protected array $data;

    /**
     * Normalizer constructor.
     * @param DataTable $datatable
     * @param array $data
     */
    public function __construct(DataTable $datatable, array $data)
    {
        $this->datatable = $datatable;
        $this->data = $data;
    }

    /**
     * @return $this
     * @throws \JsonException
     * @throws TableclothException
     */
    public function normalize(?ColumnsCollection $collection = null): self
    {
        $normalizers = [];

        if (!$collection) {
            $collection = $this->datatable->getColumnsCollection()
                ->usedColumns($this->datatable)
                ->normalizableColumns()
//                ->notVariants()
                ->notText();
        }

        $collection->each(function ($column) use (&$normalizers) {
            $normalizers[$column->getFrontEndHandle()] = $this->getNormalizer($column);
        });

        $instance = new static($this->datatable, array_map(static function ($row) use ($normalizers) {
            foreach ($normalizers as $column => $normalizer) {
                try {
                    $row[$column] = is_null($row[$column]) ?
                        $normalizer::NULL_VALUE :
                        $normalizer->normalize($row[$column]);
                } catch (\Exception $e) {
                    // could be thrown if relation is saved but relation doesnt exist anymore
                    $row[$column] = $normalizer::NULL_VALUE;
                }

            }
            return $row;
        }, $this->data));


        if ($this->datatable->isStructure() && $this->datatable->structureStrategy === 'nest') {
            $instance->toNestedStructure();
        }

        return $instance;
    }

    /**
     * @param ColumnInterface $column
     * @return NormalizerInterface
     * @throws TableclothException
     */
    protected function getNormalizer(ColumnInterface $column): NormalizerInterface
    {
        switch (true) {
            case $column->dataType === DataTypes::Boolean:
                return new BooleanNormalizer();
            case $column->isSingleList():
                return new ListNormalizer($column->getList());
            case $column instanceof AuthorColumn:
                return new AuthorNormalizer($column->getList());
            case $column->isMultiSelect():
                return new MultipleListNormalizer($column->getList());
            case $column->fieldType === Fields::Table:
                return new TableNormalizer($column->getField()->columns);
            case $column->fieldType === Fields::Time:
                return new TimeNormalizer();
            case $column->dataType === DataTypes::Date:
                return new DateNormalizer();
            case $column->isRelations():
                return new RelationsNormalizer($column->getList());
            case $column->dataType === DataTypes::Number:
                return new NumberNormalizer();
            default:
                throw new TableclothException("Normalizer not found for column " . $column->handle);
        }
    }

    public function toNestedStructure(): void
    {
        $this->data = (new StructureDataNester($this->data))->nest();
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->data;
    }
}