<?php


namespace matfish\Tablecloth\enums;


use matfish\Tablecloth\models\Column\AssetsColumn;
use matfish\Tablecloth\models\Column\AuthorColumn;
use matfish\Tablecloth\models\Column\CategoriesColumn;
use matfish\Tablecloth\models\Column\CheckboxesColumn;
use matfish\Tablecloth\models\Column\ColorColumn;
use matfish\Tablecloth\models\Column\DropdownColumn;
use matfish\Tablecloth\models\Column\EntriesColumn;
use matfish\Tablecloth\models\Column\FullNameColumn;
use matfish\Tablecloth\models\Column\MultiselectColumn;
use matfish\Tablecloth\models\Column\RadioButtonsColumn;
use matfish\Tablecloth\models\Column\TagsColumn;
use matfish\Tablecloth\models\Column\UsersColumn;

interface Fields
{
    public const Categories = 'Categories';
    public const Tags = 'Tags';
    public const Entries = 'Entries';
    public const Users = 'Users';
    public const Matrix = 'Matrix';
    public const Table = 'Table';
    public const MultiSelect = 'MultiSelect';
    public const Checkboxes = 'Checkboxes';
    public const RadioButtons = 'RadioButtons';
    public const Dropdown = 'Dropdown';
    public const Assets = 'Assets';
    public const Color = 'Color';
    public const Time = 'Time';
    public const Author = 'Author';
    public const FullName = 'FullName';

    public const Map = [
        self::Assets => AssetsColumn::class,
        self::Entries => EntriesColumn::class,
        self::Dropdown => DropdownColumn::class,
        self::Color => ColorColumn::class,
        self::Users => UsersColumn::class,
        self::Categories => CategoriesColumn::class,
        self::Tags => TagsColumn::class,
        self::MultiSelect => MultiselectColumn::class,
        self::Checkboxes => CheckboxesColumn::class,
        self::RadioButtons => RadioButtonsColumn::class,
        self::Author => AuthorColumn::class,
        self::FullName => FullNameColumn::class
    ];
}