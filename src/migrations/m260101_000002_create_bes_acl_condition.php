<?php

use yii\db\Migration;

final class m260101_000002_create_bes_acl_condition extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%bes_acl_condition}}', [
            'id'        => $this->primaryKey(),
            'entity'    => $this->string(128)->notNull(),
            'record_id' => $this->string(128)->null(),

            'effect'   => $this->string(8)->notNull(), // allow|deny
            'ops_mask' => $this->integer()->notNull()->defaultValue(0),

            'subject_json' => $this->json()->null(),
            'when_json'    => $this->json()->null(),

            'enabled'  => $this->boolean()->notNull()->defaultValue(true),
            'priority' => $this->integer()->notNull()->defaultValue(0),

            'comment' => $this->text()->null(),

            'created_at' => $this->dateTime()->notNull()->defaultExpression('NOW()'),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('NOW()'),
        ]);

        $this->createIndex('ix_bes_acl_condition_entity', '{{%bes_acl_condition}}', ['entity']);
        $this->createIndex('ix_bes_acl_condition_record', '{{%bes_acl_condition}}', ['entity', 'record_id']);
        $this->createIndex('ix_bes_acl_condition_enabled', '{{%bes_acl_condition}}', ['enabled']);
        $this->createIndex('ix_bes_acl_condition_priority', '{{%bes_acl_condition}}', ['priority']);
        $this->createIndex('ix_bes_acl_condition_effect', '{{%bes_acl_condition}}', ['effect']);
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%bes_acl_condition}}');
    }
}
