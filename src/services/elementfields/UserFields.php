<?php


namespace matfish\Tablecloth\services\elementfields;

use Craft;
use craft\base\ElementInterface;
use craft\elements\User;
use matfish\Tablecloth\enums\DataTypes;
use matfish\Tablecloth\enums\Fields;
use matfish\Tablecloth\services\elementfields\traits\CustomFieldsTrait;

class UserFields extends BaseElementFields
{

    use CustomFieldsTrait;

    protected function getElement(): ElementInterface
    {
        return new User($this->qualifiers);
    }

    protected function nativeFields(): array
    {
        return [
            [
                'name' => Craft::t('app', 'Username'),
                'handle' => 'username',
                'dataType' => DataTypes::Text,
                'type' => 'native'
            ],
            [
                'name' => Craft::t('app', 'Email'),
                'handle' => 'email',
                'dataType' => DataTypes::Text,
                'type' => 'native'
            ],
            [
                'name' => Craft::t('app', 'First Name'),
                'handle' => 'firstName',
                'dataType' => DataTypes::Text,
                'type' => 'native'
            ],
            [
                'name' => Craft::t('app', 'Last Name'),
                'handle' => 'lastName',
                'dataType' => DataTypes::Text,
                'type' => 'native'
            ],
            [
                'name' => Craft::t('app', 'Full Name'),
                'handle' => 'fullName',
                'dataType' => DataTypes::Text,
                'fieldType'=>Fields::FullName,
                'type' => 'native'
            ]
        ];
    }
}