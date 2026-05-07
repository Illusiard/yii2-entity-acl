<?php

namespace illusiard\entity_acl\services\integration;

interface EntityAclAdapterInterface
{
    public function canList(int $userId, string $entity, array $context = []): bool;

    public function canRead(int $userId, string $entity, int|string|null $recordId = null, array $context = []): bool;

    public function canCreate(int $userId, string $entity, array $context = []): bool;

    public function canUpdate(int $userId, string $entity, int|string|null $recordId = null, array $context = []): bool;

    public function canDelete(int $userId, string $entity, int|string|null $recordId = null, array $context = []): bool;

    public function canRestore(int $userId, string $entity, int|string|null $recordId = null, array $context = []): bool;

    public function canPermanentDelete(int $userId, string $entity, int|string|null $recordId = null, array $context = []): bool;
}
