<?php

namespace matfish\Tablecloth\services\dataset;


class PrefilterHandlesRetriever
{
    public function getHandles($sql): array
    {
        preg_match_all("/\{\{(.+?)\}\}/", $sql, $matches);

        return $matches[1];
    }
}