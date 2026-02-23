<?php

namespace illusiard\entity_acl\services\conditions\handlers;

use illusiard\entity_acl\models\dto\AccessRequest;
use illusiard\entity_acl\services\conditions\ConditionEngine;
use illusiard\entity_acl\services\conditions\ConditionHandlerInterface;

/**
 * 1) {"type":"conditionRef","condition":[1,2,3]}
 * 2) {"type":"conditionRef","ids":[1,2,3]}
 * 3) {"type":"conditionRef","id": 5}
 * 4) {"type":"conditionRef","condition": 5}
 */
class ConditionRefHandler implements ConditionHandlerInterface
{
    public function supports(string $type): bool
    {
        return $type === 'conditionRef' || $type === 'condition';
    }

    public function evaluate(array $payload, AccessRequest $request, ConditionEngine $engine): bool
    {
        $ids = $payload['condition'] ?? $payload['ids'] ?? null;

        if ($ids === null && isset($payload['id'])) {
            $ids = [$payload['id']];
        }

        if ($ids === null) {
            return false;
        }

        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $normalizedIds = [];
        foreach ($ids as $id) {
            $normalizedId = (int)$id;
            if ($normalizedId <= 0) {
                return false;
            }

            $normalizedIds[] = $normalizedId;
        }

        return $engine->evaluateWhen([
            ['condition' => $normalizedIds],
        ], $request);
    }
}
