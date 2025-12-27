<?php

namespace illusiard\entity_acl\services\subject;

use illusiard\entity_acl\models\dto\AccessRequest;

class ContextSubjectResolver implements SubjectResolverInterface
{
    public function resolveGroupId(int $userId, array $context = []): ?int
    {
        $gid = $context['groupId'] ?? $context['group_id'] ?? null;
        if ($gid === null) {
            return null;
        }
        return (int)$gid;
    }

    public function resolveOwnerId(AccessRequest $req): ?int
    {
        $oid = $req->context['ownerId'] ?? $req->context['owner_id'] ?? null;
        if ($oid === null) {
            return null;
        }
        return (int)$oid;
    }
}
