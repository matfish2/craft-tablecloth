<?php

namespace tableclothtests\fixtures;

use craft\base\ElementInterface;
use craft\test\fixtures\elements\BaseElementFixture;
use matfish\Tablecloth\elements\DataTable;

class DatatableDraftFixture extends BaseElementFixture
{

    public $dataFile = __DIR__ . '/data/datatable-draft.php';

    protected function createElement(): ElementInterface
    {
        return new DataTable();
    }
}