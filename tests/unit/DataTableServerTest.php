<?php

namespace tableclothtests\unit;

use Craft;
use craft\commerce\elements\Product;
use craft\elements\Asset;
use craft\elements\Entry;
use craft\fields\Number;

class DataTableServerTest extends TableclothTest
{
    /**
     * @test
     */
    public function limitsInitialDataToInitialPerPageValue(): void
    {
        $datatable = $this->getServerDatatable('entries', $this->entriesTableParams());
        $perPage = $datatable->getTableOption('initialPerPage');

        $data = $datatable->getInitialTableData();

        $this->assertCount($perPage, $data);
    }

    /**
     * @test
     */
    public function pagination()
    {
        $datatable = $this->getServerDatatable('entries', $this->entriesTableParams());
        $perPage = $datatable->getTableOption('initialPerPage');

        $data = $datatable->getData(['p' => 2]);

        $expectedId = Entry::find()->sectionId($datatable->sectionId)
            ->typeId($datatable->typeId)
            ->limit($perPage)
            ->offset($perPage)
            ->orderBy(['dateCreated' => SORT_DESC,'id'=>SORT_DESC])
            ->one()
            ->id;

        $this->assertCount($perPage, $data);
        $this->assertEquals($expectedId, $data[0]['id']);
    }

    /**
     * @test
     */
    public function sorting()
    {
        $datatable = $this->getServerDatatable('entries', array_merge($this->entriesTableParams(), [
            'columns' => [
                [
                    'handle' => 'title',
                    'heading' => 'Title',
                    'filterable' => true,
                    'sortable' => true
                ]
            ]
        ]), false);
        $data = $datatable->getData(['sortColumn' => 'id', 'sortDirection' => 'ASC']);
        $expectedId = Entry::find()->sectionId($datatable->sectionId)
            ->typeId($datatable->typeId)
            ->orderBy(['id' => SORT_ASC])
            ->one()
            ->id;

        $this->assertEquals($expectedId, $data[0]['id']);
    }

    /**
     * @test
     */
    public function filteringTextField()
    {
        $datatable = $this->getServerDatatable('entries', array_merge($this->entriesTableParams(), [
            'columns' => [
                [
                    'handle' => 'title',
                    'heading' => 'Title',
                    'filterable' => true,
                    'sortable' => true,
                    'hidden' => false
                ]
            ]
        ]), false);


        $data = $datatable->getData(['q' => 'veniam']);

        $expectedId = Entry::find()->sectionId($datatable->sectionId)
            ->typeId($datatable->typeId)
            ->search('veniam')
            ->orderBy(['dateCreated' => SORT_DESC])
            ->one()
            ->id;

        $this->assertEquals($expectedId, $data[0]['id']);
    }

    /**
     * @test
     */
    public function filteringSingleListField()
    {
        $datatable = $this->getServerDatatable('entries', array_merge($this->entriesTableParams(), [
            'columns' => [
                [
                    'handle' => 'dropdownTest',
                    'heading' => 'Dropdown',
                    'filterable' => true,
                    'sortable' => true
                ]
            ]
        ]), false);
        $data = $datatable->getData(['q' => 'Option 3']);
        $this->assertGreaterThan(0, count($data));

        foreach ($data as $row) {
            $this->assertEquals('Option 3', $row['dropdownTest']['label']);
        }
    }

    /**
     * @test
     */
    public function filteringMultiListField()
    {
        $datatable = $this->getServerDatatable('entries', array_merge($this->entriesTableParams(), [
            'columns' => [
                [
                    'handle' => 'multiSelectTest',
                    'heading' => 'Multi Select',
                    'filterable' => true,
                    'sortable' => true
                ]
            ]
        ]), false);

        $data = $datatable->getData(['q' => 'multi 3']);
        $this->assertGreaterThan(0, count($data));
        codecept_debug(count($data));
        foreach ($data as $row) {
            $labels = array_map(function ($l) {
                return $l['label'];
            }, $row['multiSelectTest']);

            $this->assertContains('Multi 3', $labels);
        }
    }

    /**
     * @test
     */
    public function filteringRelationsField()
    {
        $datatable = $this->getServerDatatable('entries', array_merge($this->entriesTableParams(), [
            'columns' => [
                [
                    'handle' => 'categoriesTest',
                    'heading' => 'Categories',
                    'filterable' => true,
                    'sortable' => true
                ]
            ]
        ]), false);

        $data = $datatable->getData(['q' => 'sports']);

        $this->assertGreaterThan(0, count($data));
        foreach ($data as $row) {
            $labels = array_map(function ($l) {
                return $l['data']['title'];
            }, $row['categoriesTest']);
            $this->assertContains('Sports', $labels);
        }
    }

    /**
     * @test
     */
    public function filteringCustomText()
    {
        $datatable = $this->getServerDatatable('entries', array_merge($this->entriesTableParams(), [
            'columns' => [
                [
                    'handle' => 'plainTextTest',
                    'heading' => 'Plain Text',
                    'filterable' => true,
                    'sortable' => true
                ],
                [
                    'handle' => 'dropdownTest',
                    'heading' => 'Dropdown',
                    'filterable' => true,
                    'sortable' => true
                ]
            ]
        ]), false);

        $data = $datatable->getData(['filters' => [
            'plainTextTest' => 'rem',
            'dropdownTest' => 'option2'
        ]]);

        $this->assertGreaterThan(0, count($data));
        codecept_debug("Found " . count($data) . " Results");
        foreach ($data as $row) {
            $this->assertEquals('option2', $row['dropdownTest']['value']);
            $this->assertStringContainsString('rem', $row['plainTextTest']);
        }
    }

    /**
     * @test
     */
    public function filteringCustomMultiple()
    {
        $datatable = $this->getServerDatatable('entries', array_merge($this->entriesTableParams(), [
            'columns' => [
                [
                    'handle' => 'checkboxesTest',
                    'heading' => 'Checkboxes Text',
                    'filterable' => true,
                    'sortable' => true
                ],
                [
                    'handle' => 'tagsTest',
                    'heading' => 'Tags Test',
                    'filterable' => true,
                    'sortable' => true
                ]
            ]
        ]), false);

        $data = $datatable->getData(['filters' => [
            'checkboxesTest' => 'checkbox1',
            'tagsTest' => 'janleb'
        ]]);

        $this->assertGreaterThan(0, count($data));
        codecept_debug("Found " . count($data) . " Results");
        foreach ($data as $row) {
            $clabels = array_map(function ($l) {
                return $l['value'];
            }, $row['checkboxesTest']);
            $tlabels = array_map(function ($l) {
                return $l['data']['title'];
            }, $row['tagsTest']);
            $this->assertContains('checkbox1', $clabels);
            $this->assertContains('janleb', $tlabels);
        }
    }

    /**
     * @test
     */
    public function filteringCustomBoolean()
    {
        $datatable = $this->getServerDatatable('entries', array_merge($this->entriesTableParams(), [
            'columns' => [
                [
                    'handle' => 'lightswitchTest',
                    'heading' => 'Lightswitch Test',
                    'filterable' => true,
                    'sortable' => true
                ],
                [
                    'handle' => 'tagsTest',
                    'heading' => 'Tags Test',
                    'filterable' => true,
                    'sortable' => true
                ]
            ]
        ]), false);

        $data = $datatable->getData(['filters' => [
            'lightswitchTest' => true,
        ]]);

        $this->assertGreaterThan(0, count($data));
        codecept_debug("Found " . count($data) . " Results");
        foreach ($data as $row) {
            $this->assertTrue($row['lightswitchTest']);
        }


        $data = $datatable->getData(['filters' => [
            'lightswitchTest' => false,
        ]]);

        $this->assertGreaterThan(0, count($data));
        codecept_debug("Found " . count($data) . " Results");
        foreach ($data as $row) {
            $this->assertFalse($row['lightswitchTest']);
        }
    }

    /**
     * @test
     */
    public function filteringCustomNumber()
    {
        $datatable = $this->getServerDatatable('entries', array_merge($this->entriesTableParams(), [
            'columns' => [
                [
                    'handle' => 'numberTest',
                    'heading' => 'Number Test',
                    'filterable' => true,
                    'sortable' => true
                ]
            ]
        ]), false);

        $data = $datatable->getData(['filters' => [
            'numberTest_*_>=' => 25
        ]]);

        $this->assertGreaterThan(0, count($data),'No Results!');
        codecept_debug("Found " . count($data) . " Results");
        foreach ($data as $row) {
            $this->assertGreaterThan(24, $row['numberTest']);
        }

        $data = $datatable->getData(['filters' => [
            'numberTest' => 32,
        ]]);

        $this->assertGreaterThan(0, count($data));
        codecept_debug("Found " . count($data) . " Results");
        foreach ($data as $row) {
            $this->assertEquals(32, $row['numberTest']);
        }

        $data = $datatable->getData(['filters' => [
            'numberTest' => [30, 50],
        ]]);

        $this->assertGreaterThan(0, count($data));
        codecept_debug("Found " . count($data) . " Results");
        foreach ($data as $row) {
            $this->assertGreaterThanOrEqual(30, $row['numberTest']);
            $this->assertLessThanOrEqual(50, $row['numberTest']);
        }
    }

    /**
     * @test
     */
    public function filteringCustomDate(): void
    {
        $datatable = $this->getServerDatatable('entries', array_merge($this->entriesTableParams(), [
            'columns' => [
                [
                    'handle' => 'dateTest',
                    'heading' => 'Date Test',
                    'filterable' => true,
                    'sortable' => true
                ]
            ]
        ]), false);

        $data = $datatable->getData(['filters' => [
            'dateTest' => ['2021-02-01', '2021-06-10']
        ]]);

        $this->assertGreaterThan(0, count($data));
        codecept_debug("Found " . count($data) . " Results");
        foreach ($data as $row) {
            $this->assertGreaterThanOrEqual('2021-02-01', $row['dateTest']);
            $this->assertLessThanOrEqual('2021-06-10', $row['dateTest']);
        }
    }


    /**
     * @test
     */
    public function entriesCount()
    {
        $this->testCount('entries', $this->entriesTableParams());
    }

    /**
     * @test
     */
    public function assetsCount()
    {
        $this->testCount('assets', $this->assetsTableParams());
    }

    /**
     * @test
     */
    public function categoriesCount()
    {
        $this->testCount('categories', $this->categoriesTableParams());
    }

    /**
     * @test
     */
    public function tagsCount()
    {
        $this->testCount('tags', $this->tagsTableParams());
    }

    /**
     * @test
     */
    public function usersCount()
    {
        $this->testCount('users', $this->usersTableParams());
    }

    /**
     * @test
     */
    public function productsCount()
    {
        Craft::$app->getPlugins()->installPlugin('commerce');

        $this->testCount('books', $this->booksTableParams());
    }

    protected function testCount($type, array $tableParams): void
    {
        $datatable = $this->getServerDatatable($type, $tableParams);
        $el = $tableParams['source'];

        $expected = $el::find()->siteId(1);

        if ($el === Entry::class) {
            $expected->sectionId($datatable->sectionId)
                ->typeId($datatable->typeId);
        } elseif ($el === Asset::class) {
            $expected->volumeId($datatable->groupId);
        } elseif ($el === Product::class) {
            $expected->typeId($datatable->typeId);
        }

        $actual = $datatable->getCount();

        $this->assertEquals($expected->count(), $actual);

    }
}