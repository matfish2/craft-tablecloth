<?php


namespace matfish\Tablecloth\services\elementfields;

use Craft;
use craft\base\ElementInterface;
use craft\elements\Entry;
use matfish\Tablecloth\enums\DataTypes;
use matfish\Tablecloth\enums\Fields;
use matfish\Tablecloth\services\elementfields\traits\CustomFieldsTrait;

class EntryFields extends BaseElementFields
{

    use CustomFieldsTrait;

    protected function nativeFields(): array
    {
        return [
            [
                'name' => Craft::t('app', 'Post Date'),
                'handle' => 'postDate',
                'dataType' => DataTypes::Date,
                'type' => 'native'
            ],
            [
                'name' => Craft::t('app', 'Author'),
                'handle' => 'authorId',
                'dataType' => DataTypes::List,
                'fieldType' => Fields::Author,
                'type' => 'native'
            ]
        ];

    }

    /**
     * @return ElementInterface
     */
    protected function getElement(): ElementInterface
    {
        return new Entry($this->qualifiers);
    }
}