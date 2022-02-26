<?php

namespace matfish\Tablecloth\elements;

use Craft;
use craft\base\Element;
use craft\commerce\elements\Product;
use craft\commerce\records\ProductType;
use craft\elements\Asset;
use craft\elements\Category;
use craft\elements\Entry;
use craft\elements\Tag;
use craft\elements\User;
use craft\fields\Matrix;
use craft\fields\Table;
use craft\web\View;
use matfish\Tablecloth\actions\DeleteAction;
use matfish\Tablecloth\collections\ColumnsCollection;
use matfish\Tablecloth\collections\TableFieldColumnsCollection;
use matfish\Tablecloth\enums\DataTypes;
use matfish\Tablecloth\enums\Fields;
use matfish\Tablecloth\enums\FieldTypes;
use matfish\Tablecloth\exceptions\TableclothException;
use matfish\Tablecloth\models\Column\ColumnInterface;
use matfish\Tablecloth\models\Column\ProductColumn;
use matfish\Tablecloth\models\Column\ShippingCategoryColumn;
use matfish\Tablecloth\models\Column\TableFieldColumn;
use matfish\Tablecloth\models\Column\TaxCategoryColumn;
use matfish\Tablecloth\models\Column\VariantColumn;
use matfish\Tablecloth\services\dataset\PrefilterSqlValidator;
use matfish\Tablecloth\services\templates\TemplateFinder;
use matfish\Tablecloth\Tablecloth;
use matfish\Tablecloth\elements\db\DataTableQuery;
use craft\elements\db\ElementQueryInterface;
use matfish\Tablecloth\models\Column\Column;
use matfish\Tablecloth\models\OverrideableSettings;
use matfish\Tablecloth\services\elementqueries\BaseSourceQuery;

class DataTable extends Element
{
    use OverrideableSettings;

    public string $name;
    public string $handle;
    public string $source = Entry::class;
    public ?string $preset = 'default';

    public bool $serverTable = false;

    // Entry
    public ?int $sectionId = null;

    // Entry \ Product
    public ?int $typeId = null;

    // Category \ Tag
    public ?int $groupId = null;

    // User
    public bool $allUsers = true;
    public $userGroups;

    // Product
    public ?string $variantsStrategy = 'nest';

    public $columns;

    public bool $filterPerColumn = false;

    // Child Row
    public bool $enableChildRows = false;
    public $childRowTableFields = [];
    public $childRowMatrixFields = [];

    // Initial Sort
    public ?string $initialSortColumn = null;
    public ?bool $initialSortAsc = true;

    public bool $overrideGeneralSettings = false;

    public ?string $datasetPrefilter = null;

    public $externalApiDetails;
    public array $fields = [];

    public array $overrideableOptions = [
        'components',
        'initialPerPage',
        'perPageValues',
        'dateFormat',
        'datetimeFormat',
        'timeFormat',
        'debounce',
        'paginationChunk'
    ];

    public ?ColumnsCollection $columnsCollection = null;

    public function rules(): array
    {
        return [
            [['name', 'handle'], 'required'],
            [['handle'], 'validateHandle'],
            [['columns'], 'required', 'when' => function ($model) {
                return !!$model->id;
            }],
            [['source'], 'required', 'when' => function ($model) {
                return !$model->id;
            }],
            [['sectionId', 'typeId'], 'required', 'when' => function ($model) {
                return $model->source === Entry::class;
            }],
            [['groupId'], 'required', 'when' => function ($model) {
                return in_array($model->source, [Category::class, Tag::class], true);
            }],
            [['userGroups'], 'validateUserGroups'],
            [['serverTable', 'filterPerColumn', 'overrideGeneralSettings', 'initialSortAsc'], 'boolean'],
            [['debounce', 'thumbnailWidth'], 'integer'],
            [['initialSortColumn', 'externalApiDetails', 'variantsStrategy'], 'string'],
            ['datasetPrefilter', 'validatePrefilter']
        ];
    }

    /**
     * @return bool
     */
    public function isNew(): bool
    {
        return !$this->id;
    }

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return \Craft::t('tablecloth', 'Table');
    }

    /**
     * @inheritdoc
     */
    public static function pluralDisplayName(): string
    {
        return \Craft::t('tablecloth', 'Tables');
    }

    /**
     * @inheritdoc
     */
    public static function refHandle(): ?string
    {
        return 'datatable';
    }

    /**
     * @return ElementQueryInterface
     */
    public static function find(): ElementQueryInterface
    {
        return new DataTableQuery(static::class);
    }

    /**
     * @param bool $isNew
     * @throws \yii\db\Exception
     */
    public function afterSave(bool $isNew): void
    {
        if ($isNew) {
            \Craft::$app->db->createCommand()
                ->insert('{{%tablecloth}}', $this->_getInsertData())
                ->execute();
        } else {
            \Craft::$app->db->createCommand()
                ->update('{{%tablecloth}}', $this->_getUpdateData(), ['id' => $this->id])
                ->execute();
        }

        parent::afterSave($isNew);
    }

    public function getTableAttributeHtml($attribute): string
    {

        $root = \Craft::getAlias('@web');
        $cpTrigger = getenv('CP_TRIGGER');
        $enabled = json_decode($this->columns, false) ? 'enabled' : '';

        switch ($attribute) {
            case 'name':
            {
                return "<span class='status $enabled'></span><a href='$root/$cpTrigger/tablecloth/tables/$this->id'>$this->name</a>";
            }
            case 'source':
            {
                $ps = explode('\\', $this->source);
                return array_pop($ps);
            }
        }

        return $this[$attribute];
    }

    public static function defineDefaultTableAttributes(string $source): array
    {
        return ['name', 'handle', 'source'];
    }

    /**
     * @return array
     */
    protected static function defineTableAttributes(): array
    {
        return [
            'id' => \Craft::t('app', 'ID'),
            'name' => \Craft::t('app', 'Name'),
            'handle' => \Craft::t('app', 'Handle'),
            'source' => \Craft::t('tablecloth', 'Source'),
        ];
    }

    protected static function defineSearchableAttributes(): array
    {
        return ['name', 'handle'];
    }

    protected static function defineActions(string $source = null): array
    {
        return [
            DeleteAction::class,
        ];
    }

    /**
     * @param string|null $context
     * @return array[]
     */
    protected static function defineSources(string $context = null): array
    {
        return [
            [
                'key' => '*',
                'label' => 'All Tables',
                'criteria' => []
            ],
        ];
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getJsData(): array
    {
        return [
            'columns' => $this->getColumnsCollection()->usedColumns($this, true)->map(function (ColumnInterface $column) {
                return [
                    'handle' => $column->getFrontEndHandle(),
                    'heading' => $column->heading,
                    'dataType' => $column->dataType,
                    'filterable' => $column->filterable,
                    'sortable' => $column->sortable,
                    'hidden' => $column->hidden,
                    'fieldType' => $column->fieldType,
                    'multiple' => $column->multiple,
                    'type' => $column->type,
                    'templatePath' => $column->templatePath,
                    'templateMode' => $column->templateMode
                ];
            })->all(),
            'options' => $this->getTableOptions(),
//            'lists' => $this->getTableLists(),
            'serverTable' => $this->serverTable
        ];
    }


    /**
     * @param $handle
     * @param array|null $columns
     * @return string
     * @throws TableclothException
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \yii\base\Exception
     */
    public function getChildRowTable($handle, ?array $columns = null): string
    {
        $field = \Craft::$app->fields->getFieldByHandle($handle);

        if (!$field) {
            throw new TableclothException("Field with handle {$handle} not found");
        }

        if (!($field instanceof Table)) {
            throw new TableclothException("Field {$handle} is not a table");
        }

        $cols = $field->columns;

        if ($columns) {
            $cols = array_filter($cols, static function ($col) use ($columns) {
                return in_array($col['handle'], $columns, true);
            });
        }

        $datatableHandle = $this->handle;

        $cols = array_map(static function ($col) use ($handle, $datatableHandle) {
            $col['datatableHandle'] = $datatableHandle;
            $col['tableFieldHandle'] = $handle;
            $column = new TableFieldColumn($col);
            $column->setTemplatePath();

            return $column;
        }, $cols);

        $collection = new TableFieldColumnsCollection($cols);

        return \Craft::$app->view->renderTemplate('tablecloth/_site/types/childRowTable', [
            'tableHandle' => $handle,
            'columns' => $collection->all()
        ], View::TEMPLATE_MODE_CP);
    }

    /**
     * @param $handle
     * @return string
     * @throws TableclothException
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \yii\base\Exception
     */
    public function getChildRowMatrix($handle): string
    {
        $field = \Craft::$app->fields->getFieldByHandle($handle);

        if (!$field) {
            throw new TableclothException("Field with handle {$handle} not found");
        }

        if (!($field instanceof Matrix)) {
            throw new TableclothException("Field {$handle} is not a matrix");
        }

        $field = \Craft::$app->fields->getFieldByHandle($handle);

        return \Craft::$app->view->renderTemplate('tablecloth/_site/template/childRowMatrix', [
            'matrix' => new \matfish\Tablecloth\models\Matrix($field, $this->handle)
        ], View::TEMPLATE_MODE_CP);
    }

    /**
     * @return string
     * @throws TableclothException
     * @throws \yii\base\Exception
     */
    public function getChildRowPath(): string
    {
        $childRowPath = "_tablecloth/tables/{$this->handle}/childRow";

        \Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_SITE);

        $view = \Craft::$app->view;

        if (!$view->doesTemplateExist($childRowPath, View::TEMPLATE_MODE_SITE)) {
            throw new TableclothException("Child row template not found at {$childRowPath}");
        }

        return $childRowPath;
    }

    /**
     * @param $component
     * @return string
     * @throws \yii\base\Exception
     */
    public function getComponentPath($component): string
    {
        return (new TemplateFinder($this))->componentPath($component);
    }


    /**
     * @param $component
     * @return string
     * @throws \yii\base\Exception
     */
    public function getDefaultComponentPath($component): string
    {
        return (new TemplateFinder($this))->defaultComponentPath($component);
    }


    /**
     * @return BaseSourceQuery
     */
    private function getQueryClass(): BaseSourceQuery
    {
        $elClass = $this->source;
        $queryClass = Tablecloth::$sourceQueries[$elClass];

        return new $queryClass($this);
    }

    /**
     * server table only
     * @return array
     */
    public function getData($params = []): array
    {
        return $this->getQueryClass()
            ->getData($params)
            ->normalize()
            ->toArray();
    }

    /**
     * @return int
     */
    public function getCount($params = []): int
    {
        return $this->getQueryClass()->getCount($params);
    }

    /**
     * @return array
     */
    public function getAllFields(): array
    {
        if (count($this->fields) === 0) {
            $elClass = $this->source;
            $fieldsClass = Tablecloth::$elementFields[$elClass];

            $this->fields = (new $fieldsClass($this->getQualifiers()))->getFields();
        }

        return array_map(function ($field) {
            $field['tableHandle'] = $this->handle;
            return $field;
        }, $this->fields);
    }

    /**
     * @return array
     */
    public function getQualifiers(): array
    {
        switch ($this->source) {
            case Entry::class:
                return [
                    'sectionId' => $this->sectionId,
                    'typeId' => $this->typeId
                ];
            case Category::class:
            case Tag::class:
                return [
                    'groupId' => $this->groupId
                ];
            case Asset::class:
                return [
                    'volumeId' => $this->groupId
                ];
            case User::class:
                return [

                ];
            case Product::class:
                return [
                    'typeId' => $this->typeId
                ];
        }
    }

    /**
     * @return array
     * @throws \JsonException
     */
    public function getInitialTableData(): array
    {
        if (!$this->columns) {
            throw new TableclothException("Table {$this->handle} is a draft. Please define columns and save");
        }

        return $this->getQueryClass()
            ->getInitialData()
            ->normalize()
            ->toArray();
    }

    /**
     * @return array
     * @throws \JsonException
     */
    public function getColumns(): array
    {
        return json_decode_if($this->columns);
    }

    /**
     * @throws \JsonException
     */
    public function getUserGroups(): array
    {
        return json_decode_if($this->userGroups);
    }


    /**
     * @return array
     * @throws TableclothException
     */
    public function getFieldsTemplates(): array
    {
        return $this->getColumnsCollection()->filter(function (Column $column) {
            return (bool)$column->templatePath;
        })->all();
    }

    /**
     * @return ColumnsCollection
     * @throws TableclothException
     */
    public function getColumnsCollection(): ColumnsCollection
    {
        // if it's not cached generate collection
        if (!$this->columnsCollection) {

            $allFields = $this->getAllFields();

            $columns = $this->getColumns();

            $tables = json_decode_if($this->childRowTableFields);

            foreach ($tables as $table) {
                $columns[] = [
                    'handle' => $table,
                    'fieldType' => Fields::Table,
                    'dataType' => DataTypes::Array,
                    'type' => FieldTypes::Custom,
                    'hidden' => true,
                    'sortable' => false,
                    'filterable' => false
                ];
            }

            $merged = custom_array_merge($allFields, $columns);

            $dbTable = $this->getDbTable();
            $source = $this->source;

            $collection = array_map(function ($column) use ($dbTable, $source) {
                $class = $this->getColumnClass($column);

                if (isset($column['fieldType']) && $column['fieldType'] === Fields::Assets) {
                    $column['thumbnailWidth'] = $this->getTableOption('thumbnailWidth');
                }

                if ($source === Product::class && $column['type'] === FieldTypes::Native) {
                    $ps = explode(':', $column['handle']);
                    $column['dbTable'] = $ps[0];
                } else {
                    $column['dbTable'] = $dbTable;
                }

                return new $class($column);
            }, $merged);

            $this->columnsCollection = new ColumnsCollection($collection);
        }

        // return a clone so that original will not change
        return clone $this->columnsCollection;
    }

    /**
     * @param $handle
     * @return Column|null
     */
    public function getColumnByHandle($handle): ?Column
    {
        return $this->getColumnsCollection()->find($handle);
    }

    /**
     * @param $option
     * @return \craft\base\ElementInterface[]|mixed|string|null
     */
    public function getTableOption($option)
    {
        // get default settings
        $defaultOption = Tablecloth::getInstance()->settings->getTableOption($option);
        $method = "get" . ucfirst($option);

        if ($this->id && $this->overrideGeneralSettings && $this->{$option}) {
            return method_exists($this, $method) ? $this->{$method}() : $this->{$option};
        }

        return $defaultOption;
    }

    /**
     * @return array
     */
    public function getTableOptions(): array
    {
        $overrideable = [];

        foreach ($this->overrideableOptions as $option) {
            $overrideable[$option] = $this->getTableOption($option);
        }


        $options = [
            'enableChildRows' => $this->enableChildRows,
            'initialSortColumn' => $this->initialSortColumn,
            'initialSortAsc' => $this->initialSortAsc
        ];

        return array_merge($overrideable, $options);
    }

    /**
     * @return array
     */
    public function getChildRowMatrixBlocks(): array
    {

        if (!$this->childRowMatrix) {
            return [];
        }

        $matrix = \Craft::$app->fields->getFieldByHandle($this->childRowMatrix);

        return array_map(function ($block) {
            return $block->handle;
        }, $matrix->getBlockTypes());
    }

    public function getEntriesLists(): ColumnsCollection
    {
        return $this->getColumnsCollection()->entriesColumns()->map(function (Column $column) {
            return [
                'handle' => $column->handle,
                'list' => $column->getList()
            ];
        });
    }

    public function getParam($param)
    {
        if (!isset($this->{$param}) || !$this->{$param}) {
            return '';
        }

        return $this->{$param};
    }

    /**
     * validates SQL prefilter
     */
    public function validatePrefilter(): void
    {
        if (!$this->datasetPrefilter) {
            return;
        }

        $validator = new PrefilterSqlValidator($this);
        $validator->validate();

        if ($errors = $validator->getErrors()) {
            foreach ($errors as $error) {
                $this->addError('datasetPrefilter', $error);
            }
        }
    }

    public function validateHandle(): void
    {
        $query = DataTable::find()->handle($this->handle);
        if ($this->id) {
            $query->where('[[tablecloth.id]]!=' . $this->id);
        }

        if ($query->exists()) {
            $this->addError('handle', 'There is already a table with this handle');
        }
    }

    public function validateUserGroups()
    {
        if ($this->source !== User::class) {
            return;
        }

        if ($this->allUsers) {
            return;
        }

        if ($this->userGroups === '[]') {
            $this->addError('userGroups', 'Please select at least one group');
        }
    }

    /**
     * @param $param
     * @return string
     * @throws \JsonException
     */
    public function getParamAsJson($param): string
    {
        $value = $this->getParam($param);

        return json_encode_if($value);
    }

    public function hasVariants()
    {
//        ecommerce_installed() &&
        return $this->source === Product::class &&
            ProductType::find()->where('[[id]]=' . $this->typeId)->one()->hasVariants;
    }

    /**
     * @throws TableclothException
     */
    protected function getDbTable(): string
    {
        switch ($this->source) {
            case Entry::class:
                $table = 'entries';
                break;
            case Category::class:
                $table = 'categories';
                break;
            case Tag::class:
                $table = 'tags';
                break;
            case Asset::class:
                $table = 'assets';
                break;
            case User::class:
                $table = 'users';
                break;
            case Product::class:
                $table = 'products';
                break;
            default:
                throw new TableclothException("Cannot find table for source {$this->source}");
        }

        return $table;
    }

    /**
     * @return array
     */
    private function _getInsertData(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'handle' => $this->handle,
            'serverTable' => $this->serverTable,
            'source' => $this->source,
            'sectionId' => $this->sectionId,
            'typeId' => $this->typeId,
            'groupId' => $this->groupId,
            'userGroups' => json_encode_if($this->userGroups),
            'variantsStrategy' => $this->variantsStrategy
        ];
    }

    /**
     * @return array
     * @throws \JsonException
     */
    private function _getUpdateData(): array
    {
        return [
            'name' => $this->name,
            'handle' => $this->handle,
            'variantsStrategy' => $this->variantsStrategy,
            'columns' => json_encode_if($this->columns),
            'serverTable' => $this->serverTable,
            'filterPerColumn' => $this->filterPerColumn,
            'enableChildRows' => $this->enableChildRows,
            'datasetPrefilter' => $this->datasetPrefilter,
            'overrideGeneralSettings' => $this->overrideGeneralSettings,
            'components' => json_encode_if($this->components),
            'childRowMatrixFields' => json_encode_if($this->childRowMatrixFields),
            'childRowTableFields' => json_encode_if($this->childRowTableFields),
            'initialPerPage' => $this->initialPerPage,
            'initialSortColumn' => $this->initialSortColumn,
            'initialSortAsc' => $this->initialSortAsc,
            'perPageValues' => $this->perPageValues,
            'debounce' => $this->debounce,
            'dateFormat' => $this->dateFormat,
            'datetimeFormat' => $this->datetimeFormat,
            'timeFormat' => $this->timeFormat,
            'paginationChunk' => $this->paginationChunk,
            'userGroups' => json_encode_if($this->userGroups),
            'thumbnailWidth' => $this->thumbnailWidth,
            'height' => $this->height,
            'preset' => $this->preset
        ];
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function getTableLists(): array
    {
        $columns = $this->getColumnsCollection()->listColumns()->all();

        $res = [];

        foreach ($columns as $column) {
            $res[$column->handle] = $column->getList();
        }

        return $res;
    }

    private function getColumnClass($column): string
    {
        if (isset($column['fieldType']) && isset(Fields::Map[$column['fieldType']])) {
            return Fields::Map[$column['fieldType']];
        }

        if ($this->source === 'craft\commerce\elements\Product' &&
            ($column['type'] === FieldTypes::Native)) {
            $ps = explode(':', $column['handle']);
            $type = $ps[0];
            $handle = $ps[1];

            if ($type === 'product') {
                if ($handle === 'taxCategoryId') {
                    return TaxCategoryColumn::class;
                }

                if ($handle === 'shippingCategoryId') {
                    return ShippingCategoryColumn::class;
                }

                return ProductColumn::class;
            }

            if ($type === 'variant') {
                return VariantColumn::class;
            }

            throw new TableclothException("Unknown type " . $type);

        }

        return Column::class;
    }
}