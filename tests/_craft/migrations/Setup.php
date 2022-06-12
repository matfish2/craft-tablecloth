<?php

namespace tableclothtests\_craft\migrations;

use tableclothtests\_craft\migrations\Migrators\FieldsMigrator;
use tableclothtests\_craft\migrations\Migrators\SectionMigrator;

class Setup extends \craft\db\Migration
{
    public function safeUp()
    {
        echo 'Migrating sections...';
        SectionMigrator::add();
        echo 'Migrating products...';
        (new ProductsMigrator)->add();
        echo 'Migrating fields...';
        FieldsMigrator::add();
    }

    public function safeDown()
    {
        SectionMigrator::remove();
    }
}