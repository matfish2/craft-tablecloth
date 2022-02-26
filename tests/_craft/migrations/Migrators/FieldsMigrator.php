<?php


namespace tableclothtests\_craft\migrations\Migrators;

use Craft;
use craft\services\Users;
use tableclothtests\_craft\migrations\services\AssetsService;
use tableclothtests\_craft\migrations\services\CategoriesService;
use tableclothtests\_craft\migrations\services\EntryTypeService;
use tableclothtests\_craft\migrations\services\FieldFactory;
use tableclothtests\_craft\migrations\services\FieldsList;
use tableclothtests\_craft\migrations\services\FieldsService;
use tableclothtests\_craft\migrations\services\ProductsService;
use tableclothtests\_craft\migrations\services\TagsService;
use tableclothtests\_craft\migrations\services\UsersService;

class FieldsMigrator extends Migrator
{
    public static function add(): bool
    {
        $s = new FieldsService();
        $fields = [];

        foreach (FieldsList::$list as $fieldClass) {
            $data = (new FieldFactory($fieldClass))->getCreatorClass()->getData();
            $fields[] = $s->add($data);
        }

        (new EntryTypeService())->addFields($fields);
        (new UsersService())->addFields($fields);
        (new CategoriesService())->addFields($fields);
        (new TagsService())->addFields($fields);
        (new AssetsService())->addFields($fields);
        (new ProductsService())->addFields($fields);

        return true;
    }

    public static function remove(): bool
    {
        return true;
    }
}