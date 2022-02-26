<?php


namespace matfish\Tablecloth\services\dataset;


class PrefilterSqlSanitizer
{
    /**
     * @param string $datasetPrefilter
     * @return string
     */
    public function sanitize(string $datasetPrefilter) : string
    {
        return preg_replace(UnsafeSqlRegex::$regex,'', $datasetPrefilter);
    }
}