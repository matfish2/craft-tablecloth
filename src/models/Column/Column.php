<?php

namespace matfish\Tablecloth\models\Column;

use craft\base\FieldInterface;
use craft\base\Model;
use craft\records\User;
use craft\web\View;
use matfish\Tablecloth\elements\DataTable;
use matfish\Tablecloth\enums\DataTypes;
use matfish\Tablecloth\enums\Fields;
use matfish\Tablecloth\enums\FieldTypes;
use matfish\Tablecloth\exceptions\TableclothException;
use matfish\Tablecloth\services\templates\TemplateFinder;
use matfish\Tablecloth\TableclothAssetBundle;

class Column extends Model implements ColumnInterface
{
    /**
     * field display name in Craft
     * @var string
     */
    public string $name;

    /**
     * @var ?int
     */
    public ?int $fieldId = null;

    /**
     * owning table handle
     * @var string
     */
    public string $tableHandle;

    /**
     * owning datatable DB table
     * @var string
     */
    public string $dbTable;

    /**
     * unique field handle in Craft
     * @var string
     */
    public string $handle;

    /**
     * datatable heading
     * @var string
     */
    public string $heading;
    /**
     * ENUM: date, list, number, text, boolean
     * @var string
     */
    public string $dataType;
    /**
     * common, native, custom
     * @var string
     */
    public string $type;
    /**
     * for custom fields. Class short name, e.g Dropdown
     * @var string
     */
    public string $fieldType = '';
    /**
     * @var bool
     */
    public bool $filterable = false;
    /**
     * @var bool
     */
    public bool $sortable;
    /**
     * @var bool
     */
    public bool $hidden;
    /**
     * @var ?string
     */

    /**
     * @var bool
     */
    public bool $multiple = false;


    /**
     * front-end template path
     * @var string|null
     */
    public ?string $templatePath = null;
    public ?string $templateMode = null;

    public ?array $columns = [];

    public const CONTEXT_FILTER = 'filter';
    public const CONTEXT_SORT = 'sort';
    public const CONTEXT_SELECT = 'select';
    public const CONTEXT_JOIN = 'join';
    public const CONTEXT_PREFILTER = 'prefilter';

    /**
     * DB table (e.g entries or categories)
     * @var ?string
     */
    protected ?string $table = null;

    public function init()
    {
        parent::init();

       $this->setTemplatePath();
    }

    public function getDatatable(): DataTable
    {
        $dt = DataTable::find()->handle($this->tableHandle)->one();

        if (!$dt) {
            throw new \Exception("Table $this->tableHandle not found");
        }

        return $dt;
    }

    public function getHandle()
    {
        return $this->isProductVariantCustomField() ? explode(':', $this->handle)[1] : $this->handle;
    }


    public function isProductVariant(): bool
    {
        return str_contains($this->handle, 'variant:');
    }

    public function isProductVariantCustomField(): bool
    {
        return $this->isCustom() && $this->isProductVariant();
    }

    /**
     * @return string
     */
    public function getFrontEndHandle(): string
    {
        if ($this->isProductVariant()) {
            if ($this->getDatatable()->variantsStrategy === 'nest') {
                return str_replace('variant:', '', $this->handle);
            }

            return str_replace(':', '__', $this->handle);
        }

        return $this->handle;
    }

    public function getContentTable(): string
    {
        return $this->isProductVariantCustomField() ? 'variant_content' : 'content';
    }

    /**
     * @return bool
     */
    public function hasTemplate(): bool
    {
        return (bool)$this->templatePath;
    }

    /**
     * @return FieldInterface
     */
    public function getField(): FieldInterface
    {
        return \Craft::$app->fields->getFieldById($this->fieldId);
    }

    /**
     * @return string
     * @throws TableclothException
     */
    public function getDbColumn(string $context = self::CONTEXT_SELECT): string
    {
        $addAlias = $context === self::CONTEXT_SELECT;

        if ($this->isCustom()) {

            $prefix = \Craft::$app->content->fieldColumnPrefix;

            $suffix = $this->getField()->columnSuffix;

            $handle = $this->getContentTable() . '.' . $prefix . $this->getHandle();

            if ($suffix) {
                $handle .= '_' . $suffix;
            }

            if ($context !== self::CONTEXT_PREFILTER) {
                $handle = "[[$handle]]";
            }

            if ($addAlias) {
                $handle .= " [[{$this->getFrontEndHandle()}]]";
            }

        } elseif (in_array($this->handle, ['dateCreated', 'dateUpdated'])) {
            $handle = "{$this->dbTable}.{$this->handle}";
        } elseif (in_array($this->handle, ['title'])) {
            $handle = "{$this->getContentTable()}.{$this->handle}";
        } elseif ($this->handle === 'slug') {
            $handle = $this->handle;
        } else {
            $handle = "{$this->dbTable}.{$this->handle}";
        }

        return $handle;
    }

    /**
     * @return bool
     */
    public function isProduct(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isList(): bool
    {
        return $this->dataType === DataTypes::List;
    }

    /**
     * @return bool
     */
    public function isSingleList(): bool
    {
        return $this->isList() && in_array($this->fieldType, [
                Fields::RadioButtons,
                Fields::Dropdown
            ], true);
    }

    /**
     * @return bool
     */
    public function isMultiSelect(): bool
    {
        return in_array($this->fieldType, [Fields::MultiSelect, Fields::Checkboxes], true);
    }

    /**
     * @return bool
     */
    public function isCustom(): bool
    {
        return $this->type === FieldTypes::Custom;
    }

    public function isSingleCustomList(): bool
    {
        return $this->isList() && $this->isCustom() && !in_array($this->fieldType, [
                Fields::Categories,
                Fields::Tags,
                Fields::Entries,
                Fields::Users,
                Fields::Assets
            ], true) && !$this->multiple;
    }

    /**
     * @return bool
     */
    public function isRelations(): bool
    {
        return in_array($this->fieldType, [
            Fields::Categories,
            Fields::Tags,
            Fields::Entries,
            Fields::Users,
            Fields::Assets
        ], true);
    }

    /**
     * @return bool
     */
    public function isNativeList(): bool
    {
        return $this->isList() && !$this->isCustom();
    }

    /**
     * @return bool
     */
    public function isNative(): bool
    {
        return !$this->isCustom();
    }

    /**
     * for Table field only
     * @return array|null
     */
    public function getColumns(): ?array
    {
        if ($this->fieldType !== Fields::Table) {
            return null;
        }

        return $this->getField()->columns;
    }

    public function getTemplatePath()
    {
        if (is_null($this->templatePath)) {
            $this->setTemplatePath();
        }

        return $this->templatePath;
    }

    public function setTemplatePath(): void
    {
        $field = strtolower($this->fieldType);
        $dataType = $this->dataType;

        $type = $dataType === DataTypes::Boolean ? 'boolean' : $field;

        $finder = new TemplateFinder($this->getDatatable());

        $fieldPath = $finder->getTemplatePath("fields/{$this->handle}");

        if ($fieldPath) {
            $this->templatePath = $fieldPath;
        } else {
            $typePath = $finder->getTemplatePath("types/{$type}");

            $this->templatePath = $typePath;
        }

        if ($this->templatePath) {
            $this->templateMode = str_contains($this->templatePath, '_tablecloth') ? View::TEMPLATE_MODE_SITE : View::TEMPLATE_MODE_CP;
        }
    }

    /**
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \yii\base\Exception
     */
    public function renderTemplate(): string
    {
        return \Craft::$app->view->renderTemplate($this->templatePath, array_merge(['value' => 'row[column.handle]', 'svgPath' => $this->getSvgPath()], $this->templateVars()), $this->templateMode);
    }

    /**
     * @return array
     */
    protected function templateVars(): array
    {
        return [];
    }

    private function getSvgPath()
    {
        $view = \Craft::$app->view;
        $bundle = TableclothAssetBundle::register($view);

        return $bundle->baseUrl . '/site/svg/';
    }

    /**
     * @return array|null
     * @throws \Exception
     */
    public function getList(): ?array
    {
        $list = [];

        if ($this->handle === 'authorId') {
            $users = User::find()->all();

            foreach ($users as $user) {
                $list[$user->id] = $user->firstName . ' ' . $user->lastName;
            }

            return $list;
        }

        return $list;
    }

}