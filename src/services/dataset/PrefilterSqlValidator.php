<?php


namespace matfish\Tablecloth\services\dataset;


use matfish\Tablecloth\elements\DataTable;
use matfish\Tablecloth\exceptions\TableclothException;

class PrefilterSqlValidator
{
    protected DataTable $dataTable;

    protected array $errors = [];

    /**
     * PrefilterSqlValidator constructor.
     * @param DataTable $dataTable
     */
    public function __construct(DataTable $dataTable)
    {
        $this->dataTable = $dataTable;
    }

    /**
     * @param $sql
     * @return bool
     */
    public function validate(): bool
    {
        $sql = $this->dataTable->datasetPrefilter;

        foreach (UnsafeSqlRegex::$regex as $regex) {
            if (preg_match($regex, $sql)) {
                $this->errors[] = 'Dangerous expression!';
            }
        }

        $handles = (new PrefilterHandlesRetriever())->getHandles($sql);

        $columns = $this->dataTable->getColumnsCollection()
            ->notRelationsColumns()
            ->notMatrixColumns()
            ->notTableColumns();

        foreach ($handles as $handle) {
            try {
                $columns->find($handle);
            } catch (TableclothException $e) {
                $this->errors[] = $e->getMessage();
            }
        }

        // try to run the query
        if (count($this->errors)===0) {
            try {
                $this->dataTable->getInitialTableData();
            } catch (\Exception $e) {
                $this->errors[] = "Invalid query";
            }
        }

        return count($this->errors)===0;
    }

    public function getErrors() {
        return array_unique($this->errors);
    }
}