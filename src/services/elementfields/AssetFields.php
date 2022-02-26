<?php


namespace matfish\Tablecloth\services\elementfields;


use Craft;
use craft\base\ElementInterface;
use craft\elements\Asset;
use matfish\Tablecloth\enums\DataTypes;
use matfish\Tablecloth\services\elementfields\traits\CustomFieldsTrait;

class AssetFields extends BaseElementFields
{
    use CustomFieldsTrait;

    protected function getElement(): ElementInterface
    {
        return new Asset($this->qualifiers);
    }

    protected function nativeFields(): array
    {
        return [
            [
                'name' => Craft::t('app', 'Filename'),
                'handle' => 'filename',
                'dataType' => DataTypes::Text,
                'type' => 'native'
            ],
            [
                'name' => Craft::t('app', 'File Kind'),
                'handle' => 'kind',
                'dataType' => DataTypes::List,
                'type' => 'native'
            ],
            [
                'name' => Craft::t('app', 'Image Width'),
                'handle' => 'width',
                'dataType' => DataTypes::Number,
                'type' => 'native'
            ],
            [
                'name' => Craft::t('app', 'Image Height'),
                'handle' => 'height',
                'dataType' => DataTypes::Number,
                'type' => 'native'
            ],
            [
                'name' => Craft::t('app', 'File Size'),
                'handle' => 'size',
                'dataType' => DataTypes::Number,
                'type' => 'native'
            ],
            [
                'name' => Craft::t('app', 'Uploaded by'),
                'handle' => 'uploaderId',
                'dataType' => DataTypes::List,
                'type' => 'native'
            ]
        ];
    }

    protected function customFields(): array
    {
        return $this->getCustomFieldsForElement($this->getElement());
    }
}