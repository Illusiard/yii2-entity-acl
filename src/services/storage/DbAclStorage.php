<?php

namespace illusiard\entity_acl\services\storage;

use illusiard\entity_acl\models\AclCondition;
use illusiard\entity_acl\models\AclRecord;

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
            ->where(['entity' => $entity, 'enabled' => 1, "ops_mask & $opMask != 0"])
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
        $row = AclCondition::findOne($id);
        return $row ? $this->mapCondition($row) : null;
    }

    private function mapAclRecord(AclRecord $row): AclRecordRow
    {
        return new AclRecordRow(
            entity: $row->entity,
            recordId: $row->record_id,
            ownerFlags: (int)$row->owner_flags,
            groupFlags: (int)$row->group_flags,
            otherFlags: (int)$row->other_flags,
            ownerId: (int)$row->owner_id,
            groupId: (int)$row->group_id,
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
            subject: $row->subject_json ? json_decode($row->subject_json, true) : [],
            when: $row->when_json ? json_decode($row->when_json, true) : [],
            enabled: (bool)$row->enabled,
            priority: (int)$row->priority,
            comment: $row->comment,
        );
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
