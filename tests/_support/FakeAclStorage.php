<?php

namespace illusiard\entity_acl\tests\_support;

use illusiard\entity_acl\services\storage\AclConditionRow;
use illusiard\entity_acl\services\storage\AclRecordRow;
use illusiard\entity_acl\services\storage\AclStorageInterface;

class FakeAclStorage implements AclStorageInterface
{
    /** @var array<string, AclRecordRow> */
    private array $acl = [];

    /** @var array<string, AclConditionRow[]> */
    private array $conds = [];

    /** @var array<int, AclConditionRow> */
    private array $condsById = [];

    public function setAcl(string $entity, ?string $recordId, AclRecordRow $row): void
    {
        $this->acl[$this->k($entity, $recordId)] = $row;
    }

    /**
     * @param AclConditionRow[] $rows
     */
    public function setConditions(string $entity, ?string $recordId, array $rows): void
    {
        $key = $this->k($entity, $recordId);
        $this->conds[$key] = $rows;

        foreach ($rows as $r) {
            $this->condsById[$r->id] = $r;
        }
    }

    public function findAclRecord(string $entity, ?string $recordId): ?AclRecordRow
    {
        if ($recordId !== null) {
            $k = $this->k($entity, $recordId);
            if (isset($this->acl[$k])) {
                return $this->acl[$k];
            }
        }

        $k = $this->k($entity, null);
        return $this->acl[$k] ?? null;
    }

    public function findConditions(string $entity, ?string $recordId, int $opMask): array
    {
        $rows = [];

        if ($recordId !== null) {
            $rows = array_merge(
                $rows,
                $this->conds[$this->k($entity, $recordId)] ?? []
            );
        }

        $rows = array_merge(
            $rows,
            $this->conds[$this->k($entity, null)] ?? []
        );

        $rows = array_values(array_filter($rows, static fn (AclConditionRow $r) => $r->enabled && ($r->opsMask & $opMask) !== 0));

        usort($rows, static fn (AclConditionRow $a, AclConditionRow $b) => $a->priority === $b->priority ? $b->id <=> $a->id : $b->priority <=> $a->priority);

        return $rows;
    }

    public function findConditionById(int $id): ?AclConditionRow
    {
        return $this->condsById[$id] ?? null;
    }

    private function k(string $entity, ?string $recordId): string
    {
        return $entity . '|' . ($recordId ?? '∅');
    }
}
