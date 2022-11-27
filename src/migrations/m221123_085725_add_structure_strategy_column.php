<?php

namespace matfish\Tablecloth\migrations;

use Craft;
use craft\db\Migration;

/**
 * m221123_085725_add_structure_strategy_column migration.
 */
class m221123_085725_add_structure_strategy_column extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        if ($this->db->tableExists('{{%tablecloth}}')) {
            $this->addColumn('{{%tablecloth}}', 'structureStrategy', 'varchar(20)');
            $this->addColumn('{{%tablecloth}}', 'structureNestingLevel', 'TINYINT');
            $this->addColumn('{{%tablecloth}}', 'isStructure', 'TINYINT(1)');
            $this->addColumn('{{%tablecloth}}', 'tcStructureId', 'INTEGER UNSIGNED');
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m221123_085725_add_structure_strategy_column cannot be reverted.\n";
        return false;
    }
}
