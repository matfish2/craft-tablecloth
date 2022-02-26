<?php

namespace tableclothtests\unit;

use Codeception\Test\Unit;

use matfish\Tablecloth\elements\DataTable;
use matfish\Tablecloth\exceptions\TableclothException;
use tableclothtests\fixtures\DatatableDraftFixture;
use UnitTester;

class DataTableDraftTest extends TableclothTest
{
    protected UnitTester $tester;

    /**
     * @return array
     */
    public function _fixtures(): array
    {
        return [
            DatatableDraftFixture::class,
        ];
    }


    /**
     * @test
     */
    public function itThrowsExceptionWhenAttemptingToLoadDraft(): void
    {
        $datatable = DataTable::find()->handle('entries')->one();
        $this->expectException(TableclothException::class);

        $datatable->getInitialTableData();
    }
}