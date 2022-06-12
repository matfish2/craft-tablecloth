<?php


namespace matfish\Tablecloth\services\elementfields;


use Craft;
use craft\base\Field;
use craft\commerce\elements\Product;
use craft\commerce\elements\Variant;
use craft\commerce\records\ProductType;
use craft\models\FieldLayout;
use matfish\Tablecloth\enums\DataTypes;
use matfish\Tablecloth\enums\FieldTypes;
use matfish\Tablecloth\services\elementfields\traits\CustomFieldsTrait;

class ProductFields extends BaseElementFields
{
    use CustomFieldsTrait;

    protected function getElement(): Product
    {
        return new Product($this->qualifiers);
    }

    protected function nativeFields(): array
    {
        return [
            [
                'name' => Craft::t('commerce', 'Free Shipping'),
                'handle' => 'product:freeShipping',
                'dataType' => DataTypes::Boolean,
                'type' => FieldTypes::Native
            ],
            [
                'name' => Craft::t('commerce', 'Promotable'),
                'handle' => 'product:promotable',
                'dataType' => DataTypes::Boolean,
                'type' => FieldTypes::Native
            ],
            [
                'name' => Craft::t('commerce', 'Available for purchase'),
                'handle' => 'product:availableForPurchase',
                'dataType' => DataTypes::Boolean,
                'type' => FieldTypes::Native
            ],
            [
                'name' => Craft::t('commerce', 'Tax Category'),
                'handle' => 'product:taxCategoryId',
                'dataType' => DataTypes::List,
                'type' => FieldTypes::Native
            ],
            [
                'name' => Craft::t('commerce', 'Shipping Category'),
                'handle' => 'product:shippingCategoryId',
                'dataType' => DataTypes::List,
                'type' => FieldTypes::Native
            ],
            [
                'name' => Craft::t('commerce', 'SKU'),
                'handle' => 'variant:sku',
                'dataType' => DataTypes::Text,
                'type' => FieldTypes::Native
            ],
            [
                'name' => Craft::t('commerce', 'Stock'),
                'handle' => 'variant:stock',
                'dataType' => DataTypes::Number,
                'type' => FieldTypes::Native
            ],
            [
                'name' => Craft::t('commerce', 'Length'),
                'handle' => 'variant:length',
                'dataType' => DataTypes::Number,
                'type' => FieldTypes::Native
            ],
            [
                'name' => Craft::t('commerce', 'Width'),
                'handle' => 'variant:width',
                'dataType' => DataTypes::Number,
                'type' => FieldTypes::Native
            ],
            [
                'name' => Craft::t('commerce', 'Height'),
                'handle' => 'variant:height',
                'dataType' => DataTypes::Number,
                'type' => FieldTypes::Native
            ],
            [
                'name' => Craft::t('commerce', 'Weight'),
                'handle' => 'variant:weight',
                'dataType' => DataTypes::Number,
                'type' => FieldTypes::Native
            ],
            [
                'name' => Craft::t('commerce', 'Price'),
                'handle' => 'variant:price',
                'dataType' => DataTypes::Number,
                'type' => FieldTypes::Native
            ],
        ];
    }

    protected function getAdditionalCustomFields(): array
    {
        $type = ProductType::find()->where("[[id]]={$this->qualifiers['typeId']}")->one();

        if ($type->hasVariants) {
            $fl = $type->getVariantFieldLayout()->one();
            $fieldLayout = new FieldLayout([
                'id' => $fl->id,
                'type' => $fl->type,
                'uid' => $fl->uid
            ]);


            if ($fieldLayout === null) {
                return [];
            }

            $fields = [];

            /** @var Field $field */
            foreach ($fieldLayout->getCustomFields() as $field) {
                $fieldConfig = $this->createFieldConfig($field, 'variant:');
                $fields[] = array_merge($fieldConfig, ['type' => 'custom']);
            }

            return $fields;
        }

        return [];
    }
}