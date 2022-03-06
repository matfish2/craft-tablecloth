<?php

namespace tableclothtests\_craft\migrations;

use tableclothtests\_craft\migrations\Migrators\FieldsMigrator;
use tableclothtests\_craft\migrations\Migrators\SectionMigrator;

class Setup extends \craft\db\Migration
{
    public function safeUp()
    {
        SectionMigrator::add();
        (new ProductsMigrator)->add();
        FieldsMigrator::add();
    }

    public function safeDown()
    {
        SectionMigrator::remove();
    }
}