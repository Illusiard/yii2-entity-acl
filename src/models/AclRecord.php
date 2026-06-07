<?php

namespace illusiard\entity_acl\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * @property int    $id
 * @property string $entity
 * @property string $record_id
 * @property int    $owner_flags
 * @property int    $group_flags
 * @property int    $other_flags
 * @property int    $owner_id
 * @property int    $group_id
 * @property int    $priority
 * @property string $created_at
 * @property string $updated_at
 */
class AclRecord extends ActiveRecord
{
    #[\Override]
    public static function tableName(): string
    {
        return '{{%bes_acl_record}}';
    }

    #[\Override]
    public function rules(): array
    {
        return [
            [['id', 'owner_flags', 'group_flags', 'other_flags', 'owner_id', 'group_id', 'priority'], 'integer'],
            [['owner_flags', 'group_flags', 'other_flags'], 'default', 'value' => 0],
            [['owner_flags', 'group_flags', 'other_flags', 'priority', 'entity'], 'required'],
            [['created_at', 'updated_at'], 'safe'],
            [['entity', 'record_id'], 'string', 'max' => 128],
            [['entity', 'record_id'], 'unique'],
        ];
    }

    #[\Override]
    public function attributeLabels(): array
    {
        return [
            'id'          => 'ID',
            'entity'      => 'Entity',
            'record_id'   => 'Record ID',
            'owner_flags' => 'Owner',
            'group_flags' => 'Group',
            'other_flags' => 'Other',
            'owner_id'    => 'Owner',
            'group_id'    => 'Group',
            'priority'    => 'Priority',
            'updated_at'  => 'Updated at',
            'created_at'  => 'Created at',
        ];
    }

    #[\Override]
    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class'              => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value'              => new Expression('NOW()'),
            ],
        ];
    }
}
