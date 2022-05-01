<?php

namespace matfish\Tablecloth\services\elementfields\traits;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\FieldInterface;
use craft\fields\BaseOptionsField;
use craft\fields\BaseRelationField;
use craft\fields\Date;
use craft\fields\Lightswitch;
use craft\fields\Number;
use craft\fields\Table;
use craft\fields\Time;
use matfish\Tablecloth\enums\DataTypes;
use matfish\Tablecloth\enums\Fields;

trait CustomFieldsTrait
{
    protected function customFields(): array
    {
        return $this->getCustomFieldsForElement($this->getElement());
    }

    /**
     * @param ElementInterface $element
     * @return array
     */
    private function getCustomFieldsForElement(ElementInterface $element): array
    {
        $fieldLayout = $element->getFieldLayout();

        if (!$element::hasContent() || $fieldLayout === null) {
            return [];
        }

        $fields = [];

        /** @var Field $field */
        foreach ($fieldLayout->getCustomFields() as $field) {
            $fieldConfig = $this->createFieldConfig($field);
            $fields[] = array_merge($fieldConfig, ['type' => 'custom']);
        }

        return array_merge($fields, $this->getAdditionalCustomFields());

    }

    /**
     * @param $field
     * @return array
     */
    protected function createFieldConfig($field, $handlePrefix = ''): array
    {
        $namePrefix = $handlePrefix ? ucfirst($handlePrefix) . ' ' : '';

        $config = [
            'name' => $namePrefix.$field->name,
            'handle' => $handlePrefix.$field->handle,
            'fieldId' => $field->id,
            'dataType' => $this->mapDataType($field),
            'fieldType' => $this->getFieldType($field),
        ];

        if ($config['fieldType'] === Fields::Table) {
            $config['columns'] = $this->getTableFieldColumns($field->handle);
        }

        if (in_array($config['fieldType'], [
            Fields::MultiSelect,
            Fields::Checkboxes,
            Fields::Tags,
            Fields::Categories,
            Fields::Entries,
            Fields::Assets,
            Fields::Users
        ], true)) {
            $config['multiple'] = true;
        }

        return $config;
    }

     /**
     * @param $handle
     * @return array
     */
    private function getTableFieldColumns($handle): array
    {
        return Craft::$app->fields->getFieldByHandle($handle)->columns;
    }

    /**
     * @return string
     */
    protected function mapDataType(Field $field): string
    {
        if ($field instanceof Lightswitch) {
            return DataTypes::Boolean;
        }

        if ($field instanceof Date) {
            return DataTypes::Date;
        }

        if ($field instanceof Time) {
            return DataTypes::Time;
        }

        if ($field instanceof BaseOptionsField || $field instanceof BaseRelationField) {
            return DataTypes::List;
        }

        if ($field instanceof Number) {
            return DataTypes::Number;
        }

        if ($field instanceof Table) {
            return DataTypes::Array;
        }

        return DataTypes::Text;
    }

     /**
     * @param FieldInterface $field
     * @return string
     */
    private function getFieldType(FieldInterface $field): string
    {
        return (new \ReflectionClass($field))->getShortName();
    }
}