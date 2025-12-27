<?php

namespace illusiard\entity_acl\services\conditions\handlers;

use illusiard\entity_acl\models\dto\AccessRequest;
use illusiard\entity_acl\services\conditions\ConditionEngine;
use illusiard\entity_acl\services\conditions\ConditionHandlerInterface;

/**
 * Поддерживаемые форматы:
 * {"type":"groupId","value":10}
 * {"type":"groupId","groupId":10}
 * {"type":"groupId","group_id":10}
 * {"type":"groupId","id":10}
 *
 * И для контекста:
 * 1) $req->context['groupId']
 * 2) $req->context['group_id']
 */
final class GroupIdHandler implements ConditionHandlerInterface
{
    public function supports(string $type): bool
    {
        return $type === 'groupId';
    }

    public function evaluate(array $payload, AccessRequest $req, ConditionEngine $engine): bool
    {
        $expected = $payload['value'] ?? $payload['groupId'] ?? $payload['group_id'] ?? $payload['id'] ?? null;
        if ($expected === null) {
            return false;
        }

        $actual = $req->context['groupId'] ?? $req->context['group_id'] ?? null;
        if ($actual === null) {
            return false;
        }

        return (int)$actual === (int)$expected;
    }
}
