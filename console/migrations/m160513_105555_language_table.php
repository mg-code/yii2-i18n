<?php

use yii\db\Migration;

class m160513_105555_language_table extends Migration
{
    public function up()
    {
        $strOptions = null;
        if ($this->db->driverName === 'mysql') {
            $strOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%language}}', [
            'iso_code' => $this->char(2)->notNull(),
            'title' => $this->string(255)->notNull(),
            'sort' => $this->smallInteger(3)->unsigned()->notNull(),
            'is_active' => $this->boolean()->unsigned()->notNull()->defaultValue(1),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->notNull(),
        ], $strOptions);

        $this->addPrimaryKey('pk', '{{%language}}', ['iso_code']);
    }

    public function down()
    {
        $this->dropTable('{{%language}}');
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}