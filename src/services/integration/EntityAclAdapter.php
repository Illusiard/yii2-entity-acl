<?php

namespace illusiard\entity_acl\services\integration;

use illusiard\entity_acl\Acl;
use illusiard\entity_acl\AclService;
use illusiard\entity_acl\models\dto\AccessRequest;

final class EntityAclAdapter implements EntityAclAdapterInterface
{
    public function canList(int $userId, string $entity, array $context = []): bool
    {
        return $this->can($userId, $entity, Acl::OPERATION_LIST, null, $context);
    }

    public function canRead(int $userId, string $entity, int|string|null $recordId = null, array $context = []): bool
    {
        return $this->can($userId, $entity, Acl::OPERATION_READ, $recordId, $context);
    }

    public function canCreate(int $userId, string $entity, array $context = []): bool
    {
        return $this->can($userId, $entity, Acl::OPERATION_CREATE, null, $context);
    }

    public function canUpdate(int $userId, string $entity, int|string|null $recordId = null, array $context = []): bool
    {
        return $this->can($userId, $entity, Acl::OPERATION_UPDATE, $recordId, $context);
    }

    public function canDelete(int $userId, string $entity, int|string|null $recordId = null, array $context = []): bool
    {
        return $this->can($userId, $entity, Acl::OPERATION_DELETE, $recordId, $context);
    }

    public function can(
        int $userId,
        string $entity,
        string $operation,
        int|string|null $recordId = null,
        array $context = []
    ): bool {
        $request = new AccessRequest(
            userId: $userId,
            entity: $entity,
            operation: $operation,
            recordId: $recordId !== null ? (string)$recordId : null,
            context: $context
        );

        return AclService::instance()->getPolicy()->can($request);
    }

    public function canRestore(int $userId, string $entity, int|string|null $recordId = null, array $context = []): bool
    {
        return $this->can($userId, $entity, Acl::OPERATION_RESTORE, $recordId, $context);
    }

    public function canPermanentDelete(int $userId, string $entity, int|string|null $recordId = null, array $context = []): bool
    {
        return $this->can($userId, $entity, Acl::OPERATION_PERMANENT_DELETE, $recordId, $context);
    }
}
