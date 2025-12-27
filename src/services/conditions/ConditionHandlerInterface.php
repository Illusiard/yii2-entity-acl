<?php

namespace illusiard\entity_acl\services\conditions;

use illusiard\entity_acl\models\dto\AccessRequest;

interface ConditionHandlerInterface
{
    public function supports(string $type): bool;

    public function evaluate(array $payload, AccessRequest $req, ConditionEngine $engine): bool;
}
