<?php

namespace illusiard\entity_acl\services\subject;

use illusiard\entity_acl\models\dto\AccessRequest;

/**
 * Interface SubjectResolverInterface
 *
 * Интерфейс для определения субъектов доступа в системе ACL:
 * - Группы пользователя (groupId), если это применимо в проекте.
 * - Владельца объекта (ownerId), с которым сравнивается owner_id в записи ACL.
 */
interface SubjectResolverInterface
{
    public function resolveGroupId(int $userId, array $context = []): ?int;

    public function resolveOwnerId(AccessRequest $req): ?int;
}