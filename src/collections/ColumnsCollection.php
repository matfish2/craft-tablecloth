<?php

namespace matfish\Tablecloth\collections;

use Closure;
use craft\fields\Table;
use matfish\Tablecloth\elements\DataTable;
use matfish\Tablecloth\enums\DataTypes;
use matfish\Tablecloth\enums\Fields;
use matfish\Tablecloth\exceptions\TableclothException;
use matfish\Tablecloth\models\Column\AuthorColumn;
use matfish\Tablecloth\models\Column\Column;
use matfish\Tablecloth\models\Column\ColumnInterface;

/**
 * Class ColumnsCollection
 * @package matfish\Tablecloth\collections
 */
class ColumnsCollection extends Collection
{
    /**
     * @return $this
     */
    public function customListColumns(): self
    {
        return $this->filter(function (Column $column) {
            return $column->isCustom() && $column->isList();
        });
    }


    /**
     * @return $this
     */
    public function customColumns(): self
    {
        return $this->filter(function (Column $column) {
            return $column->isCustom();
        });
    }


    /**
     * @return $this
     */
    public function normalizableColumns(): self
    {
        return $this->filter(function (Column $column) {
            return $column->isCustom() || $column->isProduct() || $column instanceof AuthorColumn;
        });
    }

    /**
     * @return $this
     */
    public function filterable(): self
    {
        return $this->filter(function (Column $column) {
            return $column->filterable;
        });
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->columns);
    }

    /**
     * @return $this
     */
    public function listColumns(): self
    {
        return $this->filter(function ($column) {
            return $column->dataType === DataTypes::List;
        });
    }

    /**
     * @return $this
     */
    public function dateColumns(): self
    {
        return $this->filter(function ($column) {
            return $column->dataType === DataTypes::Date;
        });
    }

    /**
     * @return $this
     */
    public function booleanColumns(): self
    {
        return $this->filter(function ($column) {
            return $column->dataType === DataTypes::Boolean;
        });
    }

    /**
     * @return $this
     */
    public function numberColumns(): self
    {
        return $this->filter(function ($column) {
            return $column->dataType === DataTypes::Number;
        });
    }

    /**
     * @return $this
     */
    public function multiselectColumns(): self
    {
        return $this->filter(function (Column $column) {
            return $column->isMultiselect();
        });
    }

    /**
     * @return $this
     */
    public function relationsColumns(): self
    {
        return $this->filter(function (Column $column) {
            return in_array($column->fieldType, [
                Fields::Users,
                Fields::Categories,
                Fields::Tags,
                Fields::Entries
            ], true);
        });
    }

    /**
     * @return $this
     */
    public function notRelationsColumns(): self
    {
        return $this->filter(function (Column $column) {
            return !in_array($column->fieldType, [
                Fields::Users,
                Fields::Categories,
                Fields::Tags,
                Fields::Entries
            ], true);
        });
    }

    /**
     * @return $this
     */
    public function notTableColumns(): self
    {
        return $this->filter(function (Column $column) {
            return $column->fieldType !== Fields::Table;
        });
    }

    /**
     * @return $this
     */
    public function categoryColumns(): self
    {
        return $this->filter(function (Column $column) {
            return $column->fieldType === Fields::Categories;
        });
    }

    /**
     * @return $this
     */
    public function assetsColumns(): self
    {
        return $this->filter(function (Column $column) {
            return $column->fieldType === Fields::Assets;
        });
    }

    /**
     * @return $this
     */
    public function notMatrixColumns(): self
    {
        return $this->filter(function (Column $column) {
            return $column->fieldType !== Fields::Matrix;
        });
    }

    public function variants() : self {
        return $this->filter(function(Column $column) {
            return $column->isProductVariant();
        });
    }

    public function notVariants() : self {
        return $this->filter(function(Column $column) {
            return !$column->isProductVariant();
        });
    }

    public function matrixColumns(): self
    {
        return $this->filter(function (Column $column) {
            return $column->fieldType === Fields::Matrix;
        });
    }

    /**
     * @return $this
     */
    public function tagColumns(): self
    {
        return $this->filter(function (Column $column) {
            return $column->fieldType === Fields::Tags;
        });
    }

    /**
     * @return $this
     */
    public function entriesColumns(): self
    {
        return $this->filter(function (Column $column) {
            return $column->fieldType === Fields::Entries;
        });
    }

    /**
     * @return $this
     */
    public function usersColumns(): self
    {
        return $this->filter(function (Column $column) {
            return $column->fieldType === Fields::Users;
        });
    }

    /**
     * @throws \JsonException
     */
    public function usedColumns(DataTable $datatable, $excludeTables = false, $excludeVariantFields = false) : self {
         $used = array_map(static function($column) {
            return $column['handle'];
        },$datatable->getColumns());

        return $this->filter(static function(ColumnInterface $column) use ($used, $excludeTables, $excludeVariantFields) {
            return (in_array($column->handle, $used, true) ||
                   (!$excludeTables && $column->fieldType===Fields::Table)) &&
                   !($excludeVariantFields && $column->isProductVariant());
        })->sort($used);
    }

    public function sort(array $handles) : self {
        usort($this->columns, static function($a, $b) use ($handles) {
            return array_search($a->handle, $handles, true) > array_search($b->handle, $handles, true) ? 1 : -1;
        });

        return $this;
    }

    /**
     * get DB columns for SQL query
     * @return Column[]
     */
    public function dbColumns(): array
    {
        return $this->filter(function($column) {
            return $column->handle!=='id';
        })->map(function (ColumnInterface $column) {
            return $column->getDbColumn();
        })->all();
    }

    public function notNative(): self
    {
        return $this->filter(function (Column $column) {
            return $column->isNative();
        });
    }

    /**
     * @return $this
     */
    public function notText(): self
    {
        return $this->filter(function (Column $column) {
            return $column->dataType !== DataTypes::Text;
        });
    }


    /**
     * @return $this
     */
    public function notSingleList(): self
    {
        return $this->filter(function (Column $column) {
            return !$column->isSingleList();
        });
    }

    /**
     * @return $this
     */
    public function handles(): self
    {
        return $this->map(function ($column) {
            return $column->handle;
        });
    }

    /**
     * @param Closure $callback
     * @return $this
     */
    public function filter(Closure $callback): self
    {
        return new static(array_values(array_filter($this->columns, $callback)));
    }

    /**
     * @param Closure $callback
     * @return $this
     */
    public function map(Closure $callback): self
    {
        return new static(array_map($callback, $this->columns));
    }

    /**
     * @param Closure $callback
     */
    public function each(Closure $callback): void
    {
        foreach ($this->columns as $column) {
            $callback($column);
        }
    }

    /**
     * @param $handle
     * @return ?Column
     */
    public function find($handle): ?Column
    {
        if (str_contains($handle,'variant__')) {
            $handle = str_replace('variant__','variant:', $handle);
        }

        $res = $this->filter(function ($column) use ($handle) {
            return $column->handle === $handle;
        })->all();

        if (count($res) === 0) {
            throw new TableclothException("Cannot find column with handle {$handle}.");
        }

        return $res[0];
    }
}