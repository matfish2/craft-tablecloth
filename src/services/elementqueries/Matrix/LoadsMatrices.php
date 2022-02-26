<?php


namespace matfish\Tablecloth\services\elementqueries\Matrix;


trait LoadsMatrices
{
    /**
     * @param $data
     * @throws \JsonException
     */
    protected function attachMatrices($data): array
    {
        $matrices = json_decode_if($this->dataTable->childRowMatrixFields);

        foreach ($matrices as $matrix) {
            // handle 'variant:' prefix
            $ps = explode(':', $matrix);
            $handle = array_pop($ps);
            $field = \Craft::$app->getFields()->getFieldByHandle($handle);
            $dataIds = $this->getDataIds($data);
            $matrixData = (new MatrixQuery($field, $this->dataTable->siteId))->getData($dataIds);
            $data = (new MatrixCombiner($field, $data, $matrixData, $this->dataTable))->combine();
        }

        return $data;
    }

    /**
     * @param array $results
     * @return array
     */
    private function getDataIds(array $results): array
    {
        return array_map(static function ($row) {
            return $row['id'];
        }, $results);
    }

}