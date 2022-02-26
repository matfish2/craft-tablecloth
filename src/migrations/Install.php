<?php

namespace matfish\Tablecloth\migrations;

use craft\db\Migration;

class Install extends Migration
{
    public function safeUp()
    {
        if (!$this->db->tableExists('{{%tablecloth}}')) {
            $this->createTable('{{%tablecloth}}', [
                'id' => $this->integer()->notNull(),
                'name' => $this->string()->notNull(),
                'handle' => $this->string()->notNull(),
                'source' => $this->string()->notNull(),
                'sectionId' => $this->integer()->null(),
                'typeId'=>$this->integer()->null(),
                'groupId'=>$this->integer()->null(),
                'allUsers'=>$this->boolean()->defaultValue(true),
                'userGroups'=>$this->string()->null(),
                'variantsStrategy'=>$this->enum('variantsStrategy',['join','nest'])->null(),
                'externalApiDetails' => $this->text()->null(),
                'serverTable' => $this->boolean()->defaultValue(false),
                'columns' => $this->text()->null(),
                'filterPerColumn' => $this->boolean()->defaultValue(false),
                'initialSortColumn' => $this->string()->null(),
                'initialSortAsc' => $this->boolean()->null(),
                'enableChildRows' => $this->boolean()->defaultValue(false),
                'childRowMatrixFields' => $this->text()->null(),
                'childRowTableFields' => $this->text()->null(),
                'datasetPrefilter'=>$this->text()->null(),
                'overrideGeneralSettings' => $this->boolean()->defaultValue(false),
                'components' => $this->text()->null(),
                'debounce' => $this->smallInteger()->unsigned()->null(),
                'perPageValues' => $this->text()->null(),
                'paginationChunk' => $this->smallInteger()->unsigned()->null(),
                'thumbnailWidth'=>$this->smallInteger()->unsigned()->null(),
                'height'=>$this->smallInteger()->unsigned()->null(),
                'preset'=>$this->string()->null(),
                'initialPerPage' => $this->smallInteger()->unsigned()->null(),
                'dateFormat' => $this->string(20)->null(),
                'datetimeFormat' => $this->string(20)->null(),
                'timeFormat' => $this->string(20)->null(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
                'PRIMARY KEY(id)',
            ]);

            // give it a foreign key to the elements table
            $this->addForeignKey(
                $this->db->getForeignKeyName(),
                '{{%tablecloth}}', 'id', '{{%elements}}', 'id', 'CASCADE', null);
        }
    }

    public function safeDown()
    {
        if ($this->db->tableExists('{{%tablecloth}}')) {
            $this->dropTable('{{%tablecloth}}');
        }
    }
}