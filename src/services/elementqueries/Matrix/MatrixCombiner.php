<?php


namespace matfish\Tablecloth\services\elementqueries\Matrix;

use craft\fields\Assets;
use craft\fields\BaseOptionsField;
use craft\fields\BaseRelationField;
use craft\fields\Categories;
use craft\fields\Checkboxes;
use craft\fields\Date;
use craft\fields\Dropdown;
use craft\fields\Entries;
use craft\fields\Lightswitch;
use craft\fields\Matrix;
use craft\fields\MultiSelect;
use craft\fields\RadioButtons;
use craft\fields\Table;
use craft\fields\Tags;
use craft\fields\Time;
use craft\fields\Users;
use matfish\Tablecloth\elements\DataTable;
use matfish\Tablecloth\enums\DataTypes;
use matfish\Tablecloth\services\normalizers\BooleanNormalizer;
use matfish\Tablecloth\services\normalizers\DateNormalizer;
use matfish\Tablecloth\services\normalizers\ListNormalizer;
use matfish\Tablecloth\services\normalizers\MultipleListNormalizer;
use matfish\Tablecloth\services\normalizers\RelationsNormalizer;
use matfish\Tablecloth\services\normalizers\TableNormalizer;
use matfish\Tablecloth\services\normalizers\TimeNormalizer;

/**
 * Class MatrixCombiner
 * @package matfish\Tablecloth\services\elementqueries
 */
class MatrixCombiner
{
    protected Matrix $field;
    protected array $data;
    protected array $nestedData;
    protected DataTable $datatable;

    protected array $fieldsMap = [];

    protected array $normalizersMap = [
        Dropdown::class => ListNormalizer::class,
        RadioButtons::class => ListNormalizer::class,
        Date::class => DateNormalizer::class,
        Checkboxes::class => MultipleListNormalizer::class,
        MultiSelect::class => MultipleListNormalizer::class,
        Time::class => TimeNormalizer::class,
        Lightswitch::class => BooleanNormalizer::class,
        Assets::class => RelationsNormalizer::class,
        Categories::class => RelationsNormalizer::class,
        Tags::class => RelationsNormalizer::class,
        Entries::class => RelationsNormalizer::class,
        Users::class => RelationsNormalizer::class,
        Table::class => TableNormalizer::class
    ];

    /**
     * NestedStructureCombiner constructor.
     * @param Matrix $field
     * @param array $data
     * @param array $nestedData
     * @param DataTable $datatable
     */
    public function __construct(Matrix $field, array $data, array $nestedData, DataTable $datatable)
    {
        $this->field = $field;
        $this->data = $data;
        $this->nestedData = $nestedData;
        $this->datatable = $datatable;
    }


    public function combine(): array
    {
        $this->fieldsMap = $this->createFieldsMap();

        $data = $this->attachOwnerIdAsKey($this->data, 'id');
        $nestedData = $this->attachOwnerIdAsKey($this->nestedData, 'ownerId');

        foreach ($nestedData as $key => $blocks) {

            if (!isset($data[$key][$this->field->handle])) {
                $data[$key][$this->field->handle] = [];
            }

            $data[$key][$this->field->handle] = $this->transformBlocks($blocks);
        }

        return array_values($data);
    }

    /**
     * @param array $array
     * @param string $idKey
     * @return array
     */
    private function attachOwnerIdAsKey(array $array, string $idKey)
    {
        $res = [];

        foreach ($array as $row) {
            $id = $row[$idKey];
            if ($idKey === 'id') {
                // all rows should have the matrix field handle
                $row[$this->field->handle] = [];
                $res[$id] = $row;
            } else {
                $res[$id][] = $row;
            }
        }

        return $res;
    }

    private function transformBlocks($blocks): array
    {
        $res = [];

        foreach ($blocks as $block) {
            $handle = $block['handle'];
            $row = [
                'handle' => $handle
            ];

            foreach ($block as $key => $value) {
                $id = "{$this->field->columnPrefix}{$handle}_";
                if (str_contains($key, $id)) {
                    $fieldHandle = explode($id, $key)[1];

                    $row[$fieldHandle] = $this->getValue($fieldHandle, $value);
                }
            }

            $res[] = $row;
        }

        return $res;
    }

    /**
     * @throws \Exception
     */
    private function createFieldsMap(): array
    {
        $map = [];
        $fields = $this->field->getBlockTypeFields();

        foreach ($fields as $field) {

            $cls = get_class($field);

            $map[$field->handle] = [
                'class' => $cls
            ];

            if ($field instanceof BaseOptionsField) {
                $map[$field->handle]['options'] = $this->listAsMap($field->options);
            } elseif ($field instanceof BaseRelationField) {
                $shortName = (new \ReflectionClass($cls))->getShortName();
                $class = "matfish\\tablecloth\\models\\Column\\{$shortName}Column";

                $data = [
                    'dataType' => DataTypes::List,
                    'handle' => $field->handle,
                    'fieldId' => $field->id,
                    'tableHandle' => $this->datatable->handle
                ];

                if ($cls === Assets::class) {
                    $data['thumbnailWidth'] = $this->datatable->getTableOption('thumbnailWidth');
                }

                $i = new $class($data);

                $map[$field->handle]['options'] = $i->getList();

            } elseif ($field instanceof Table) {
                $map[$field->handle]['options'] = $field->columns;
            }
        }

        return $map;
    }

    private function getValue($fieldHandle, $value)
    {
        if (!$value) {
            return in_array($this->fieldsMap[$fieldHandle]['class'], [
                Entries::class,
                Categories::class,
                Users::class,
                Assets::class,
                Tags::class
            ], true) ? [] : null;
        }

        $data = $this->fieldsMap[$fieldHandle];
        $cls = $data['class'];

        if (!isset($this->normalizersMap[$cls])) {
            return $value;
        }

        $nrm = $this->normalizersMap[$cls];
        $c = $data['options'] ?? [];

        return (new $nrm($c))->normalize($value);
    }

    private function listAsMap($options)
    {
        $res = [];

        foreach ($options as $option) {
            $res[$option['value']] = $option['label'];
        }

        return $res;
    }
}