<?php

namespace illusiard\entity_acl\services\conditions\handlers;

use illusiard\entity_acl\services\conditions\ConditionEngine;
use illusiard\entity_acl\services\conditions\ConditionHandlerInterface;
use illusiard\entity_acl\models\dto\AccessRequest;

final class AlwaysHandler implements ConditionHandlerInterface
{
    public function supports(string $type): bool
    {
        return $type === 'always';
    }

    public function evaluate(array $payload, AccessRequest $request, ConditionEngine $engine): bool
    {
        return true;
    }
}
