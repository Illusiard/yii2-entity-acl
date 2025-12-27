<?php

namespace illusiard\entity_acl\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * @property int    $id
 * @property string $entity
 * @property string $record_id
 * @property string $subject_json
 * @property string $when_json
 * @property string $comment
 * @property int    $effect
 * @property int    $ops_mask
 * @property int    $enabled
 * @property int    $priority
 * @property string $created_at
 * @property string $updated_at
 */
class AclCondition extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%bes_acl_condition}}';
    }

    public function rules(): array
    {
        return [
            [['id', 'priority', 'ops_mask', 'enabled'], 'integer'],
            [['ops_mask'], 'default', 'value' => 0],
            [['enabled'], 'default', 'value' => 1],
            [['priority', 'entity', 'effect', 'ops_mask'], 'required'],
            [['created_at', 'updated_at'], 'safe'],
            [['entity', 'record_id'], 'string', 'max' => 128],
            [['effect'], 'string', 'max' => 8],
            [['comment', 'subject_json', 'when_json'], 'string'],
            [['entity', 'record_id'], 'unique'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id'           => 'ID',
            'entity'       => 'Entity',
            'record_id'    => 'Record ID',
            'effect'       => 'Effect',
            'ops_mask'     => 'Ops mask',
            'subject_json' => 'Subject',
            'when_json'    => 'When',
            'enabled'      => 'Enabled',
            'comment'      => 'Comment',
            'priority'     => 'Priority',
            'updated_at'   => 'Updated at',
            'created_at'   => 'Created at',
        ];
    }

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
