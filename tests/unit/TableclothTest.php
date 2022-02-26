<?php

namespace tableclothtests\unit;

use Codeception\Test\Unit;
use Craft;
use craft\commerce\elements\Product;
use craft\commerce\records\ProductType;
use craft\elements\Asset;
use craft\elements\Category;
use craft\elements\Entry;
use craft\elements\Tag;
use craft\elements\User;
use craft\fields\Matrix;
use craft\fields\Table;
use craft\records\CategoryGroup;
use craft\records\TagGroup;
use craft\records\Volume;
use matfish\Tablecloth\elements\DataTable;
use matfish\Tablecloth\services\elementfields\AssetFields;
use matfish\Tablecloth\services\elementfields\CategoryFields;
use matfish\Tablecloth\services\elementfields\EntryFields;
use matfish\Tablecloth\services\elementfields\ProductFields;
use matfish\Tablecloth\services\elementfields\TagFields;
use matfish\Tablecloth\services\elementfields\UserFields;
use tableclothtests\_craft\migrations\services\FieldFactory;
use tableclothtests\_craft\migrations\services\FieldsList;
use UnitTester;

class TableclothTest extends Unit
{

    protected UnitTester $tester;

    public function _after()
    {
        $tables = DataTable::find()->all();

        foreach ($tables as $table) {
            Craft::$app->elements->deleteElement($table, true);
        }
    }

    protected function deleteDatatable(DataTable $table)
    {
        Craft::$app->elements->deleteElement($table, true);
    }

    protected function getDatatable(string $type = 'entries', array $params = [], $mergeColumns = true): DataTable
    {

        $t = DataTable::find()->one();

        if ($t) {
            $this->deleteDatatable($t);
        }

        $method = "{$type}TableParams";
        $params = array_merge($params, $this->$method());

        $datatable = new DataTable();

        $columns = $mergeColumns ? array_merge($this->getColumns($type, $params), $params['columns'] ?? []) : $params['columns'];

        $datatable->columns = $columns;
        $datatable->enableChildRows = true;
        $datatable->childRowMatrixFields = ['matrixTest'];
        $datatable->childRowTableFields = ['tableTest'];

        foreach ($params as $key => $value) {
            if ($key === 'datasetPrefilter') {
                continue;
            }
            $datatable[$key] = $value;
        }

        $res = \Craft::$app->elements->saveElement($datatable);

        if (!$res) {
            throw new \Exception("Failed" . json_encode($datatable->getErrors()));
        }

        if (isset($params['datasetPrefilter'])) {
            $datatable->datasetPrefilter = $params['datasetPrefilter'];
            \Craft::$app->elements->saveElement($datatable);
        }

        return $datatable;
    }

    protected function getServerDatatable($type, array $params = [], $mergeColumns = true): DataTable
    {
        return $this->getDatatable($type, array_merge(['serverTable' => true], $params), $mergeColumns);
    }

    protected function getColumns($type, $params)
    {
        $columns = [];

        foreach (FieldsList::$list as $cls) {
            if (in_array($cls, [Matrix::class, Table::class], true)) {
                continue;
            }

            $data = (new FieldFactory($cls))->getFieldData();

//            if (isset($params['variantsStrategy']) && $params['variantsStrategy'] === 'nest' && str_contains($data['handle'], 'variant')) {
//var_dump($data);die;
//
//                continue;
//            }

            $column = [
                'handle' => $data['handle'],
                'heading' => $data['name'],
                'sortable' => true,
                'filterable' => true,
                'hidden' => false,
            ];


            $columns[] = $column;

        }

        $map = [
            'entries' => EntryFields::class,
            'categories' => CategoryFields::class,
            'tags' => TagFields::class,
            'users' => UserFields::class,
            'assets' => AssetFields::class,
            'books' => ProductFields::class,
            'phones' => ProductFields::class
        ];

        return array_merge($this->getBuiltInColumns($map[$type], $params), $columns);
    }

    protected function getBuiltInColumns($el, $params)
    {
        $fields = (new $el([]))->getBuiltInFields();

        if (isset($params['variantsStrategy']) && $params['variantsStrategy'] === 'nest') {
            $fields = array_filter($fields, function ($field) {
                return !str_contains($field['handle'], 'variant');
            });
        }

        return array_map(static function ($field) {
            return [
                'handle' => $field['handle'],
                'heading' => $field['name'],
                'sortable' => true,
                'filterable' => true,
                'hidden' => false,
            ];
        }, $fields);
    }

    protected function categoriesTableParams()
    {
        return [
            'name' => 'Categories',
            'handle' => 'categories',
            'source' => Category::class,
            'groupId' => CategoryGroup::find()->one()->id
        ];
    }

    protected function tagsTableParams()
    {
        return [
            'name' => 'Tags',
            'handle' => 'tags',
            'source' => Tag::class,
            'groupId' => TagGroup::find()->one()->id
        ];
    }

    protected function assetsTableParams()
    {
        return [
            'name' => 'Assets',
            'handle' => 'assets',
            'source' => Asset::class,
            'groupId' => Volume::find()->one()->id
        ];
    }

    protected function usersTableParams()
    {
        return [
            'name' => 'Users',
            'handle' => 'users',
            'source' => User::class
        ];
    }

    protected function entriesTableParams()
    {
        $section = Craft::$app->sections->getSectionByHandle('posts');

        return [
            'name' => 'Entries',
            'handle' => 'entries',
            'source' => Entry::class,
            'sectionId' => $section->id,
            'typeId' => $section->getEntryTypes()[0]->id,
        ];
    }

    protected function booksTableParams()
    {
        return $this->productsTableParams('books');
    }


    protected function phonesTableParams()
    {
        return $this->productsTableParams('phones');
    }

    protected function productsTableParams($type)
    {
        $typeId = ProductType::find()->where("[[handle]]='$type'")->one()->id;

        return [
            'name' => 'Products',
            'handle' => 'products',
            'source' => Product::class,
            'typeId' => $typeId,
        ];
    }

    protected function getElementClass($el)
    {
        return Craft::$app->db->driverName === 'pgsql' ?
            'craft\\elements\\' . $el :
            'craft\\\\elements\\\\' . $el;
    }
}