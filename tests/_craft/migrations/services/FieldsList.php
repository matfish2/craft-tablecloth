<?php


namespace tableclothtests\_craft\migrations\services;


use craft\fields\Assets;
use craft\fields\Categories;
use craft\fields\Checkboxes;
use craft\fields\Color;
use craft\fields\Date;
use craft\fields\Dropdown;
use craft\fields\Email;
use craft\fields\Entries;
use craft\fields\Lightswitch;
use craft\fields\Matrix;
use craft\fields\MultiSelect;
use craft\fields\Number;
use craft\fields\PlainText;
use craft\fields\RadioButtons;
use craft\fields\Table;
use craft\fields\Tags;
use craft\fields\Time;
use craft\fields\Url;
use craft\fields\Users;

class FieldsList
{
    public static array $list = [
        PlainText::class,
        Color::class,
        Url::class,
        Number::class,
        Email::class,
        Dropdown::class,
        MultiSelect::class,
        Checkboxes::class,
        RadioButtons::class,
        Date::class,
        Time::class,
        Lightswitch::class,
        Assets::class,
        Categories::class,
        Tags::class,
        Entries::class,
        Users::class,
        Table::class,
        Matrix::class,
    ];

    public static array $tableFields = [
        'singleline',
        'color',
        'url',
        'email',
        'select',
        'checkbox',
        'date',
        'time',
        'lightswitch',
        'multiline',
        'number'
    ];

}