<?php

namespace tableclothtests\unit;

use Craft;
use craft\base\Plugin;
use craft\commerce\elements\Product;
use craft\commerce\elements\Variant;
use craft\elements\Asset;
use craft\elements\Entry;
use craft\fields\Matrix;
use tableclothtests\_craft\migrations\services\FieldFactory;
use tableclothtests\_craft\migrations\services\FieldsList;

class DataTableTest extends TableclothTest
{
    /**
     * @test
     * @throws \Exception
     */
    public function loadsInitialDataEntrySource(): void
    {
        $this->testInitialData('entries');
    }

    /**
     * @test
     * @throws \Exception
     */
    public function loadsInitialDataUsersSource(): void
    {
        $this->testInitialData('users');
    }

    /**
     * @test
     * @throws \Exception
     */
    public function loadsInitialDataAssetsSource(): void
    {
        $this->testInitialData('assets');
    }

    /**
     * @test
     * @throws \Exception
     */
    public function loadsInitialDataCategoriesSource(): void
    {
        $this->testInitialData('categories');
    }

    /**
     * @test
     * @throws \Exception
     */
    public function loadsInitialDataTagsSource(): void
    {
        $this->testInitialData('tags');
    }

    /**
     * @test
     * @throws \Exception
     */
    public function loadsInitialDataProductsNoVariantsSource(): void
    {
        Craft::$app->getPlugins()->installPlugin('commerce');
        $this->testInitialData('books');
    }

    /**
     * @test
     * @throws \Exception
     */
    public function loadsInitialDataProductsWithVariantsNestedSource(): void
    {
        Craft::$app->getPlugins()->installPlugin('commerce');

        $this->testInitialData('phones', [
            'variantsStrategy' => 'nest'
        ]);
    }

    /**
     * @test
     * @throws \Exception
     */
//    public function loadsInitialDataProductsWithVariantsJoinedSource(): void
//    {
//        $this->testInitialData('phones', [
//            'variantsStrategy' => 'join'
//        ]);
//    }

    /**
     * @test
     */
    public function presentsColumnsInUserSelectedOrder(): void
    {
        $datatable = $this->getDatatable();
        $expected = array_map(static function ($column) {
            return $column['handle'];
        }, $datatable->getColumns());

        $jsData = $datatable->getJsData();

        $actual = array_map(static function ($column) {
            return $column['handle'];
        }, $jsData['columns']);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function sortsByCreatedAtDescDefault(): void
    {
        $datatable = $this->getDatatable();
        $data = $datatable->getInitialTableData();
        $firstId = $data[0]['id'];
        $max = Entry::find()
            ->typeId(1)
            ->sectionId(1)
//            ->enabledForSite()
            ->select('entries.id')
            ->orderBy([
                'entries.dateCreated' => SORT_DESC,
                'elements.id' => SORT_ASC])
            ->one();

        $this->assertEquals($max->id, $firstId);
    }

    /**
     * @test
     */
    public function sortsInitiallyBySelectedColumn(): void
    {
        $datatable = $this->getDatatable('entries', [
            'initialSortColumn' => 'postDate',
            'initialSortAsc' => true
        ]);

        $data = $datatable->getInitialTableData();
        $actual = $data[0]['postDate'];

        $expected = Entry::find()
            ->typeId(1)
            ->sectionId(1)
//            ->enabledForSite()
            ->select('entries.postDate')
            ->orderBy('entries.postDate ASC')
            ->one()
            ->postDate
            ->setTimezone(new \DateTimeZone('GMT'))
            ->format('Y-m-d H:i:s');

        $this->assertEquals($expected, $actual);

        $datatable = $this->getDatatable('entries', [
            'initialSortColumn' => 'numberTest',
            'initialSortAsc' => false,
        ]);

        $data = $datatable->getInitialTableData();
        $actual = $data[0]['numberTest'];

        $field = \Craft::$app->fields->getFieldByHandle('numberTest');
        $prefix = \Craft::$app->content->fieldColumnPrefix;
        $dbcol = $prefix . 'numberTest_' . $field->columnSuffix;

        $elClass = $this->getElementClass('Entry');
        $expected = \Craft::$app->db->createCommand("SELECT max([[{$dbcol}]]) [[x]] from {{%content}} [[content]] JOIN {{%elements}} [[elements]] ON [[elements]].[[id]]=[[content]].[[elementId]] WHERE [[elements]].[[type]]='{$elClass}'")->queryOne();

        $this->assertEquals($expected['x'], $actual);
    }

    /**
     * @test
     */
//    public function prefiltersDatasetByUserDefinedQuery()
//    {
//        $dt = $this->getDatatable('entries', [
//            'datasetPrefilter' => "DATE({{postDate}})>'2021-01-01'"
//        ]);
//
//        $actual = $dt->getInitialTableData();
//        $expected = Entry::find()->where("[[postDate]]>'2021-01-01 23:59:00'")->count();
//        codecept_debug("Found " . count($actual) . " entries");
//        $this->assertCount($expected, $actual);
//
//        $dt = $this->getDatatable('entries', [
//            'datasetPrefilter' => "{{numberTest}}>100"
//        ]);
//
//        $actual = $dt->getInitialTableData();
//        $field = \Craft::$app->fields->getFieldByHandle('numberTest');
//        $prefix = \Craft::$app->content->fieldColumnPrefix;
//        $dbcol = $prefix . 'numberTest_' . $field->columnSuffix;
//
//        $driver = \Craft::$app->db->driverName;
//        $x = $driver === 'pgsql' ? 'craft\\elements\\Entry' : 'craft\\\\elements\\\\Entry';
//        $expected = \Craft::$app->db->createCommand("SELECT count(*) [[N]] from {{%content}} [[content]]
//JOIN {{%elements}} [[elements]] ON [[elements]].[[id]]=[[content]].[[elementId]]
//WHERE [[elements]].[[type]]='$x' AND [[revisionId]] IS NULL AND [[$dbcol]]>100")->queryOne();
//        codecept_debug("Found " . count($actual) . " entries");
//        $this->assertCount($expected['N'], $actual, 'Number failed');
//    }

    /**
     * @throws \craft\errors\InvalidFieldException
     * @throws \JsonException
     * @throws \Exception
     */
    protected function testInitialData($type, $params = []): void
    {
        $datatable = $this->getDatatable($type, $params);

        $data = $datatable->getInitialTableData();

        $el = $datatable->source;

        $elementsCount = $el::find()->siteId(1);

        if ($el === Entry::class) {
            $elementsCount->sectionId($datatable->sectionId)
                ->typeId($datatable->typeId);
        } elseif ($el === Asset::class) {
            $elementsCount->volumeId($datatable->groupId);
        } elseif ($el === Product::class) {
            $elementsCount->typeId($datatable->typeId);
        }

        $elementsCount = $elementsCount->count();
        codecept_debug("Found $elementsCount elements");
        $key = 'id';/// $el===Product::class ? 'productId' : 'id';
        $firstElement = $el::find()->id($data[0][$key])
            ->siteId(1)
            ->one();


        foreach (FieldsList::$list as $cls) {
            $f = (new FieldFactory($cls));
            $val = $f->getRetrieverClass()->get($firstElement);
            $handle = $f->getFieldData()['handle'];

            $this->assertEquals($val, $data[0][$handle], $handle);
        }

        // Nested variants test
        if ($type === 'phones' && $params['variantsStrategy'] === 'nest') {
            $firstVariantData = $data[0]['variants'][0];
            $firstVariant = Variant::find()->id($firstVariantData['id'])->one();

            foreach (FieldsList::$list as $cls) {
                $f = (new FieldFactory($cls));
                $val = $f->getRetrieverClass()->get($firstVariant);
                $handle = $f->getFieldData()['handle'];

                $this->assertEquals($val, $firstVariantData[$handle], 'variant: ' . $handle);
            }
        }

        $this->assertCount($elementsCount, $data);
    }
}