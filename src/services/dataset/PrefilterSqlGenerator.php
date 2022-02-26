<?php

namespace matfish\Tablecloth\services\dataset;

use matfish\Tablecloth\elements\DataTable;
use matfish\Tablecloth\models\Column\Column;

class PrefilterSqlGenerator
{
    protected DataTable $dataTable;

    /**
     * PrefilterDataset constructor.
     * @param DataTable $dataTable
     */
    public function __construct(DataTable $dataTable)
    {
        $this->dataTable = $dataTable;
    }

    /**
     * @throws \matfish\Tablecloth\exceptions\TableclothException
     */
    public function get() : string {
        // SQL is sanitized both on insert and retrieve
        // to be absolutely sure no malicious SQL injection occurs
        $sql = (new PrefilterSqlSanitizer())->sanitize($this->dataTable->datasetPrefilter);

        $handles = (new PrefilterHandlesRetriever())->getHandles($sql);

        $columns = $this->dataTable->getColumnsCollection()
            ->notRelationsColumns()
            ->notMatrixColumns()
            ->notTableColumns();

        foreach ($handles as $handle) {
            $column = $columns->find($handle);
            $dbColumn = $column->getDbColumn(Column::CONTEXT_PREFILTER);

            $sql = str_replace("{{" . $handle . "}}","[[$dbColumn]]",$sql);
        }

        return $sql;
    }
}