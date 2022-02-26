<?php

namespace matfish\Tablecloth\services\elementfields;

use Craft;
use craft\base\ElementInterface;
use matfish\Tablecloth\enums\DataTypes;

abstract class BaseElementFields
{
    protected array $qualifiers = [];

    /**
     * BaseElementFields constructor.
     * @param array $qualifiers
     */
    public function __construct(array $qualifiers)
    {
        $this->qualifiers = $qualifiers;
    }


    public function getFields(): array
    {
        return array_merge($this->commonFields(), $this->nativeFields(), $this->customFields());
    }

    public function getBuiltInFields() : array {
        return array_merge($this->commonFields(), $this->nativeFields());
    }

    abstract protected function getElement(): ElementInterface;

    protected function commonFields(): array
    {
        return [
            [
                'name' => Craft::t('app', 'ID'),
                'handle' => 'id',
                'dataType' => DataTypes::Number,
                'type' => 'common'
            ],
            [
                'name' => Craft::t('app', 'Title'),
                'handle' => 'title',
                'dataType' => DataTypes::Text,
                'type' => 'common'
            ],
            [
                'name' => Craft::t('app', 'Slug'),
                'handle' => 'slug',
                'dataType' => DataTypes::Text,
                'type' => 'common'
            ],
            [
                'name' => Craft::t('app', 'Date Created'),
                'handle' => 'dateCreated',
                'dataType' => DataTypes::Date,
                'type' => 'common'
            ],
            [
                'name' => Craft::t('app', 'Date Updated'),
                'handle' => 'dateUpdated',
                'dataType' => DataTypes::Date,
                'type' => 'common'
            ],
        ];
    }

    abstract protected function nativeFields(): array;

    protected function customFields(): array
    {
        return [];
    }

    protected function getAdditionalCustomFields() : array {
        return [];
    }
}