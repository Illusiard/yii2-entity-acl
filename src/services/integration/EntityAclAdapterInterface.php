<?php

namespace illusiard\entity_acl\services\integration;

use illusiard\entity_acl\models\dto\AccessDecision;

interface EntityAclAdapterInterface
{
    public function canList(int $userId, string $entity, array $context = []): bool;

    public function canRead(int $userId, string $entity, int|string|null $recordId = null, array $context = []): bool;

    public function canCreate(int $userId, string $entity, array $context = []): bool;

    public function canUpdate(int $userId, string $entity, int|string|null $recordId = null, array $context = []): bool;

    public function canDelete(int $userId, string $entity, int|string|null $recordId = null, array $context = []): bool;

    public function canRestore(int $userId, string $entity, int|string|null $recordId = null, array $context = []): bool;

    public function canPermanentDelete(int $userId, string $entity, int|string|null $recordId = null, array $context = []): bool;

    public function decide(int $userId, string $entity, mixed $operation, ?string $recordId = null, array $context = [], bool $withTrace = false): AccessDecision;
}
