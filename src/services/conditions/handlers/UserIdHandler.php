<?php

namespace illusiard\entity_acl\services\conditions\handlers;

use illusiard\entity_acl\models\dto\AccessRequest;
use illusiard\entity_acl\services\conditions\ConditionEngine;
use illusiard\entity_acl\services\conditions\ConditionHandlerInterface;

/**
 * Поддерживаемые форматы:
 * {"type":"userId","value":1}
 * {"type":"userId","userId":1}
 * {"type":"userId","user_id":1}
 * {"type":"userId","id":1}
 */
final class UserIdHandler implements ConditionHandlerInterface
{
    public function supports(string $type): bool
    {
        return $type === 'userId';
    }

    public function evaluate(array $payload, AccessRequest $req, ConditionEngine $engine): bool
    {
        $expected = $payload['value'] ?? $payload['userId'] ?? $payload['user_id'] ?? $payload['id'] ?? null;
        if ($expected === null) {
            return false;
        }

        return (int)$req->userId === (int)$expected;
    }
}
