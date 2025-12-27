<?php

namespace illusiard\entity_acl\services\storage;

interface AclStorageInterface
{
    public function findAclRecord(string $entity, ?string $recordId): ?AclRecordRow;

    /**
     * @return AclConditionRow[] sorted by priority desc, id desc
     */
    public function findConditions(string $entity, ?string $recordId, int $opMask): array;

    public function findConditionById(int $id): ?AclConditionRow;
}
