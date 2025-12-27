<?php

use yii\db\Migration;

final class m260101_000001_create_bes_acl_record extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%bes_acl_record}}', [
            'id'        => $this->primaryKey(),
            'entity'    => $this->string(128)->notNull(),
            'record_id' => $this->string(128)->null(),

            'owner_flags' => $this->integer()->notNull()->defaultValue(0),
            'group_flags' => $this->integer()->notNull()->defaultValue(0),
            'other_flags' => $this->integer()->notNull()->defaultValue(0),

            'owner_id' => $this->integer()->null(),
            'group_id' => $this->integer()->null(),

            'priority' => $this->integer()->notNull()->defaultValue(0),

            'created_at' => $this->dateTime()->notNull()->defaultExpression('NOW()'),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('NOW()'),
        ]);

        $this->createIndex('ux_bes_acl_record_entity_record', '{{%bes_acl_record}}', ['entity', 'record_id'], true);

        $this->createIndex('ix_bes_acl_record_entity', '{{%bes_acl_record}}', ['entity']);
        $this->createIndex('ix_bes_acl_record_record', '{{%bes_acl_record}}', ['entity', 'record_id']);
        $this->createIndex('ix_bes_acl_record_owner', '{{%bes_acl_record}}', ['owner_id']);
        $this->createIndex('ix_bes_acl_record_group', '{{%bes_acl_record}}', ['group_id']);
        $this->createIndex('ix_bes_acl_record_priority', '{{%bes_acl_record}}', ['priority']);
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%bes_acl_record}}');
    }
}
