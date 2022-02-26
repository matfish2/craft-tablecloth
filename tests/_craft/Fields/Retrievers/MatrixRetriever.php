<?php


namespace tableclothtests\_craft\Fields\Retrievers;


use craft\fields\Assets;
use craft\fields\BaseRelationField;
use craft\fields\Categories;
use craft\fields\data\ColorData;
use craft\fields\data\MultiOptionsFieldData;
use craft\fields\data\SingleOptionFieldData;
use craft\fields\Entries;
use craft\fields\Table;
use craft\fields\Tags;
use craft\fields\Time;
use craft\fields\Users;
use DateTime;
use matfish\Tablecloth\services\normalizers\TableNormalizer;

class MatrixRetriever extends FieldValueRetriever
{

    public function transform($value)
    {
        $res = [];

        foreach ($value->all() as $block) {
            $handle = $block->type->handle;
            $values = $block->fieldValues;
            $fields = $block->type->fields;
            $vals = [];

            foreach ($values as $k => $v) {
                $field = $this->getFieldByHandle($fields, $k);

                if ($field instanceof Assets) {
                    $vals[$k] = (new AssetsRetriever([]))->transform($v);
                } elseif ($v instanceof SingleOptionFieldData) {
                    $vals[$k] = $this->getListValue($v);
                } elseif ($v instanceof MultiOptionsFieldData) {
                    $vals[$k] = $this->getListValues($v);
                } elseif ($field instanceof Time) {
                    $vals[$k] = (new TimeRetriever([]))->transform($v);
                } elseif ($v instanceof Datetime) {
                    $vals[$k] = $v->format('Y-m-d H:i:s');
                } elseif ($field instanceof Table) {
                    $vals[$k] = (new TableNormalizer($field->columns))->normalize($this->transformTableToDbFormat($v));
                } elseif ($field instanceof Categories) {
                    $vals[$k] = (new CategoriesRetriever([]))->transform($v);
                } elseif ($field instanceof Tags) {
                  $vals[$k] = (new TagsRetriever([]))->transform($v);
                } elseif ($field instanceof Entries) {
                  $vals[$k] = (new EntriesRetriever([]))->transform($v);
                } elseif ($field instanceof Users) {
                  $vals[$k] = (new UsersRetriever([]))->transform($v);
                } else {
                    $vals[$k] = $v;
                }
            }

            $b = array_merge($vals, ['handle' => $handle]);

            $res[] = $b;
        }

        return $res;
    }

    private function getFieldByHandle($fields, $k)
    {
        return array_values(array_filter($fields, static function ($f) use ($k) {
            return $f->handle === $k;
        }))[0];
    }

    private function getListValue(SingleOptionFieldData $v)
    {
        $selected = array_values(array_filter($v->getOptions(), static function ($o) {
            return $o->selected;
        }))[0];

        return [
            'label' => $selected->label,
            'value' => $selected->value,
        ];
    }

    private function getListValues(MultiOptionsFieldData $v)
    {
        $selected = array_values(array_filter($v->getOptions(), function ($o) {
            return $o->selected;
        }));

        return array_map(static function ($s) {
            return [
                'label' => $s->label,
                'value' => $s->value,
            ];
        }, $selected);
    }

    private function transformTableToDbFormat($table)
    {
        $res = [];

        foreach ($table as $row) {
            $r = [];

            foreach ($row as $column => $value) {
                if (0 !== strpos($column, "col")) {
                    break;
                }
                switch (true):
                    case $value instanceof ColorData:
                        $val = $value->getHex();
                        break;
                    case $value instanceof DateTime:
                        $val = $value->setTimezone(new \DateTimeZone('GMT'))->format('Y-m-d H:i:s');
                        break;
                    default:
                        $val = $value;
                endswitch;

                $r[$column] = $val;
            }
            $res[] = $r;
        }

        return $res;
    }
}