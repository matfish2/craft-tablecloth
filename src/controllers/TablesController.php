<?php


namespace matfish\Tablecloth\controllers;


use Craft;
use craft\commerce\elements\Product;
use craft\commerce\records\ProductType;
use craft\elements\Asset;
use craft\elements\Category;
use craft\elements\Tag;
use craft\elements\User;
use craft\fields\Matrix;
use craft\models\Section;
use craft\records\CategoryGroup;
use craft\elements\Entry;
use craft\records\TagGroup;
use craft\records\UserGroup;
use craft\records\Volume;
use craft\web\Controller;
use craft\web\View;
use matfish\Tablecloth\enums\Fields;
use matfish\Tablecloth\enums\FieldTypes;
use matfish\Tablecloth\exceptions\TableclothException;
use matfish\Tablecloth\Tablecloth;
use matfish\Tablecloth\elements\DataTable;

class TablesController extends Controller
{
    protected array $params = [
        'name' => 'string',
        'handle' => 'string',
        'serverTable' => 'boolean',
        'filterPerColumn' => 'boolean',
        'enableChildRows' => 'boolean',
        'childRowTemplate' => 'string',
        'debounce' => 'integer',
        'childRowMatrixFields' => 'array',
        'childRowTableFields' => 'array',
        'overrideGeneralSettings' => 'boolean',
        'components' => 'array',
        'initialPerPage' => 'integer',
        'perPageValues' => 'array',
        'dateFormat' => 'string',
        'datetimeFormat' => 'string',
        'timeFormat' => 'string',
        'initialSortColumn' => 'string',
        'initialSortAsc' => 'boolean',
        'datasetPrefilter' => 'string',
        'paginationChunk' => 'integer',
        'allUsers' => 'boolean',
        'userGroups' => 'array',
        'thumbnailWidth' => 'integer',
        'height' => 'integer',
        'preset' => 'string',
        'variantsStrategy'=>'string'
    ];

    /**
     * @throws \JsonException
     */
    public function actionEdit(): \yii\web\Response
    {
        $datatable = $this->getDataTableForEdit();

        if (!$datatable) {
            return $this->redirect('tablecloth');
        }

        $data = [
            'datatable' => $datatable,
            'sources' => $this->getSourcesList(),
            'sections' => $this->getSectionsList(),
            'productTypes' => $this->getProductTypesList(),
            'categoryGroups' => $this->getCategoryGroups(),
            'tagGroups' => $this->getTagGroups(),
            'userGroups' => $this->getUserGroups(),
            'assetVolumes' => $this->getAssetVolumes(),
            'presets' => $this->getPresets()
        ];

        // for a saved table where the source is defined load the fields
        if ($datatable->id) {
            $data = array_merge($data, $this->getFieldsList($datatable));
        }

        return $this->renderTemplate('tablecloth/cp/_edit', $data, View::TEMPLATE_MODE_CP);
    }

    /**
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionSave()
    {
        // Ensure the user has permission to save events
        $this->requirePermission('edit-tablecloth');

        $tableId = $this->request->getBodyParam('tableId');

        $datatable = $tableId ? DataTable::findOne($tableId) : new DataTable();

        if (!$tableId) {
            $source = $this->request->getBodyParam('source');
            $datatable->source = $source;
            switch ($source) {
                case Entry::class:
                    $datatable->sectionId = $this->request->getBodyParam('sectionId');
                    $datatable->typeId = $this->request->getBodyParam('typeId');
                    break;
                case Category::class:
                case Tag::class:
                    $datatable->groupId = $this->request->getBodyParam('groupId');
                    break;
                case Asset::class:
                    $datatable->groupId = $this->request->getBodyParam('volumeId');
                    break;
                case User::class:
                    $datatable->userGroups = $this->request->getBodyParam('userGroups');
                    break;
                case Product::class:
                    $datatable->typeId = $this->request->getBodyParam('typeId');
                    break;
                default:
                    throw new TableclothException("Unknown source {$source}");
            }

        }

        $datatable = $this->setParamsFromRequest($datatable);
        $datatable->columns = $this->getColumnsFromRequest($datatable);

        // Try to save it
        if (!Craft::$app->elements->saveElement($datatable)) {
            $this->setFailFlash(Craft::t('tablecloth', 'Failed to save table'));
            // Send the event back to the edit action
            Craft::$app->urlManager->setRouteParams([
                'datatable' => $datatable,
            ]);

            return null;
        }

        $this->setSuccessFlash(Craft::t('tablecloth', 'DataTable saved.'));

        if ($tableId) {
            $this->redirect('tablecloth');
        } else {
            $this->redirect('tablecloth/tables/' . $datatable->id . '/#columns');
        }
    }

    /**
     * @param DataTable $dataTable
     * @return array
     * @throws \JsonException
     */
    private function getFieldsList(DataTable $datatable): array
    {
        $fieldsClass = Tablecloth::$elementFields[$datatable->source];
        $fields = (new $fieldsClass($datatable->getQualifiers()))->getFields();

        $normalFields = [];
        $variantFields = [];
        // i.e Matrix and Table
        $matrixFields = [];
        $tableFields = [];

        array_map(static function ($field) use (&$normalFields, &$matrixFields, &$tableFields, &$variantFields, $datatable) {
            $value = [
                'label' => $field['name'],
                'value' => $field['handle'],
                'dataType' => $field['dataType'],
            ];
            if ($field['type'] === 'custom' && $field['fieldType'] === Fields::Table) {
                $tableFields[] = $value;
            } elseif ($field['type'] === 'custom' && $field['fieldType'] === Fields::Matrix) {
                $matrixFields[] = $value;
            } elseif ($datatable->variantsStrategy === 'nest' && str_contains($field['handle'], 'variant:')) {
                $variantFields[] = $value;
            } else {
                $normalFields[] = $value;
            }
        }, $fields);

        $fieldsMap = [];

        foreach ($fields as $field) {
            $fieldsMap[$field['handle']] = $field['name'];
        }

        return [
            'normalFields' => $normalFields,
            'matrixFields' => $matrixFields,
            'tableFields' => $tableFields,
            'variantFields' => $variantFields,
            'fieldsMap' => json_encode($fieldsMap, JSON_THROW_ON_ERROR) // for JS use only
        ];
    }

    /**
     * @return array
     */
    private function getSourcesList(): array
    {
        $keys = array_keys(Tablecloth::$elementFields);

        if (!ecommerce_installed()) {
            $keys = array_filter($keys, function ($key) {
                return $key !== Product::class;
            });
        }

        return array_map(static function ($key) {

            $ps = explode('\\', $key);
            $label = array_pop($ps);

            return [
                'label' => $label,
                'value' => $key
            ];
        }, $keys);
    }

    /**
     * @return array
     */
    private function getSectionsList(): array
    {
        $channel = Craft::$app->sections->getSectionsByType(Section::TYPE_CHANNEL);
        $structure = Craft::$app->sections->getSectionsByType(Section::TYPE_STRUCTURE);

        return array_map(static function ($section) {
            return [
                'label' => $section->name,
                'value' => $section->id,
                'entryTypes' => array_map(static function ($entryType) {
                    return [
                        'label' => $entryType->name,
                        'value' => $entryType->id
                    ];
                }, $section->getEntryTypes())
            ];
        }, array_merge($channel, $structure));
    }

    /**
     * @return array
     */
    private function getProductTypesList(): array
    {

        if (!ecommerce_installed()) {
            return [];
        }

        $res = [];

        foreach (ProductType::find()->all() as $type) {
            $res[] = [
                'label' => $type->name,
                'value' => $type->id,
                'hasVariants' => (bool)$type->hasVariants
            ];
        }

        return $res;
    }

    /**
     * @return array
     */
    private function getColumnsFromRequest(DataTable $dataTable): array
    {
        $columns = $this->request->getBodyParam('columns');
        $columns = array_filter($columns, function($column) {
            return !!$column['handle'];
        });

        if (!$columns) {
            return [];
        }

        $columnsMap = $this->getColumnsMap($dataTable);

        return array_map(static function ($column) use ($columnsMap) {
            $columnData = $columnsMap[$column['handle']];
            $column['filterable'] = $column['filterable'] === 'true';
            $column['sortable'] = $column['sortable'] === 'true';
            $column['hidden'] = $column['hidden'] === 'true';

            if ($columnData['type'] === FieldTypes::Custom) {
                $ps = explode(':', $column['handle']);
                $handle = array_pop($ps);
                $column['fieldId'] = Craft::$app->fields->getFieldByHandle($handle)->id;
            }
            return $column;
        }, $columns);
    }

    /**
     * @param $handle
     * @return array
     */
    private function getMatrixBlockHandles($handle): array
    {
        /**
         * @var Matrix
         */
        $field = Craft::$app->fields->getFieldByHandle($handle);

        return array_map(static function ($block) {
            return $block->handle;
        }, $field->getBlockTypes());
    }

    /**
     * @throws \JsonException
     * @throws \yii\base\InvalidConfigException
     */
    private function setParamsFromRequest(DataTable $datatable): DataTable
    {
        $values = Craft::$app->request->getBodyParams();

        foreach ($this->params as $key => $type) {
            if (!isset($values[$key])) {
                continue;
            }
            $value = $values[$key];

            if ($type === 'boolean') {
                $value = (bool)$value;
            } elseif ($type === 'array') {
                $value = json_encode_if($value);
            } elseif ($type === 'integer') {
                $value = (int)$value;
            }

            $datatable->{$key} = $value;
        }

        return $datatable;
    }

    private function getDataTableForEdit(): DataTable
    {
        $routeParams = Craft::$app->urlManager->getRouteParams();

        // failed request, return old model to repopulate form
        if (isset($routeParams['datatable']) && $routeParams['datatable']) {
            return $routeParams['datatable'];
        }

        // edit
        if (isset($routeParams['tableId']) && $routeParams['tableId']) {
            return DataTable::findOne($routeParams['tableId']);
        }

        // create
        return new DataTable();
    }

    /**
     * @return array
     */
    private function getCategoryGroups(): array
    {
        return array_map(static function ($c) {
            return [
                'label' => $c->name,
                'value' => $c->id,
            ];
        }, CategoryGroup::find()->all());
    }

    /**
     * @return array
     */
    private function getTagGroups(): array
    {
        return array_map(static function ($c) {
            return [
                'label' => $c->name,
                'value' => $c->id,
            ];
        }, TagGroup::find()->all());
    }

    /**
     * @return array
     */
    private function getUserGroups(): array
    {
        return array_map(static function ($c) {
            return [
                'label' => $c->name,
                'value' => $c->id,
            ];
        }, UserGroup::find()->all());
    }


    private function getAssetVolumes()
    {
        return array_map(static function ($c) {
            return [
                'label' => $c->name,
                'value' => $c->id,
            ];
        }, Volume::find()->all());
    }

    private function getColumnsMap(DataTable $datatable): array
    {
        $fields = $datatable->getAllFields();

        $map = [];

        foreach ($fields as $field) {
            $map[$field['handle']] = $field;
        }

        return $map;
    }

    private function getPresets(): array
    {
        $presets = ['default'];
        $path = Craft::$app->path->getSiteTemplatesPath() . '/_tablecloth/presets';

        if (is_dir($path)) {
            $f = scandir($path);
            $f = array_filter($f, function ($x) {
                return !in_array($x, ['.', '..', 'default']);
            });

            $presets = array_merge($presets, $f);
        }

        return array_map(static function ($preset) {
            return [
                'label' => $preset,
                'value' => $preset
            ];
        }, $presets);
    }
}