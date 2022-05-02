<?php

namespace matfish\Tablecloth\services\elementqueries;


use matfish\Tablecloth\elements\DataTable;
use matfish\Tablecloth\services\normalizers\Normalizer;

abstract class BaseSourceQuery
{
    protected DataTable $dataTable;
    protected TableclothQuery $builder;
    protected ?int $siteId;

    public function __construct(DataTable $dataTable)
    {
        $this->dataTable = $dataTable;
        $this->siteId = $this->dataTable->siteId;
    }

    /**
     * Client tables - fetch all the data upfront
     * Server tables - fetch first page
     * @return Normalizer
     */
    abstract public function getInitialData() : Normalizer;

    /**
     * Server table only - get total count
     * @return int
     */
    abstract public function getCount($params = []): int;

    /**
     * Server table only - get data
     * @return array
     */
    abstract public function getData(array $params = []): Normalizer;
}