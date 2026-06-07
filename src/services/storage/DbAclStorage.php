<?php

namespace illusiard\entity_acl\services\storage;

use illusiard\entity_acl\models\AclCondition;
use illusiard\entity_acl\models\AclRecord;
use JsonException;
use yii\db\Expression;

final class DbAclStorage implements AclStorageInterface
{
    /** @var array<string, AclRecordRow|null> */
    private array $cacheAclRecord = [];

    /** @var array<string, AclConditionRow[]> */
    private array $cacheConditions = [];

    /** @var array<int, AclConditionRow|null> */
    private array $cacheConditionById = [];

    public function findAclRecord(string $entity, ?string $recordId): ?AclRecordRow
    {
        $key = $this->buildAclRecordKey($entity, $recordId);
        if (array_key_exists($key, $this->cacheAclRecord)) {
            return $this->cacheAclRecord[$key];
        }

        $row = AclRecord::find()
            ->where(['entity' => $entity])
            ->andWhere(['or', ['record_id' => $recordId], ['record_id' => null]])
            ->orderBy(['record_id' => SORT_DESC, 'priority' => SORT_DESC, 'id' => SORT_DESC])
            ->one();

        return $this->cacheAclRecord[$key] = ($row ? $this->mapAclRecord($row) : null);
    }

    /**
     * @param string      $entity
     * @param string|null $recordId
     * @param int         $opMask
     *
     * @return AclConditionRow[]
     */
    public function findConditions(string $entity, ?string $recordId, int $opMask): array
    {
        $key = $this->buildAclConditionKey($entity, $recordId, $opMask);
        if (array_key_exists($key, $this->cacheConditions)) {
            return $this->cacheConditions[$key];
        }

        $rows = AclCondition::find()
            ->where(['entity' => $entity, 'enabled' => 1])
            ->andWhere(new Expression('([[ops_mask]] & :opMask) != 0', [':opMask' => $opMask]))
            ->andWhere(['or', ['record_id' => $recordId], ['record_id' => null]])
            ->orderBy(['priority' => SORT_DESC, 'id' => SORT_DESC])
            ->all();

        $out = [];
        foreach ($rows as $row) {
            $cond = $this->mapCondition($row);
            $out[] = $cond;

            $this->cacheConditionById[$cond->id] = $cond;
        }

        return $this->cacheConditions[$key] = $out;
    }

    public function findConditionById(int $id): ?AclConditionRow
    {
        if (array_key_exists($id, $this->cacheConditionById)) {
            return $this->cacheConditionById[$id];
        }

        $row = AclCondition::findOne($id);
        return $this->cacheConditionById[$id] = ($row ? $this->mapCondition($row) : null);
    }

    private function mapAclRecord(AclRecord $row): AclRecordRow
    {
        return new AclRecordRow(
            entity: $row->entity,
            recordId: $row->record_id,
            ownerFlags: (int)$row->owner_flags,
            groupFlags: (int)$row->group_flags,
            otherFlags: (int)$row->other_flags,
            ownerId: (int)$row->owner_id ?: null,
            groupId: (int)$row->group_id ?: null,
            priority: (int)$row->priority,
        );
    }

    private function mapCondition(AclCondition $row): AclConditionRow
    {
        return new AclConditionRow(
            id: (int)$row->id,
            entity: $row->entity,
            recordId: $row->record_id,
            effect: $row->effect,
            opsMask: (int)$row->ops_mask,
            subject: $this->decodeJsonArray($row->subject_json),
            when: $this->decodeJsonArray($row->when_json),
            enabled: (bool)$row->enabled,
            priority: (int)$row->priority,
            comment: $row->comment,
        );
    }

    /**
     * @return array<array-key, mixed>
     * @throws JsonException
     */
    private function decodeJsonArray(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_array($value)) {
            return $value;
        }

        if (!is_string($value)) {
            return [];
        }

        $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);

        return is_array($decoded) ? $decoded : [];
    }

    public function clearCache(): void
    {
        $this->cacheAclRecord = [];
        $this->cacheConditions = [];
        $this->cacheConditionById = [];
    }

    private function buildAclRecordKey(string $entity, ?string $recordId): string
    {
        return $entity . '|' . ($recordId ?? '∅');
    }

    private function buildAclConditionKey(string $entity, ?string $recordId, int $opMask): string
    {
        return $entity . '|' . ($recordId ?? '∅') . '|' . $opMask;
    }
}
