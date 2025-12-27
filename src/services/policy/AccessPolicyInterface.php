<?php

namespace illusiard\entity_acl\services\policy;

use illusiard\entity_acl\models\dto\AccessDecision;
use illusiard\entity_acl\models\dto\AccessRequest;

interface AccessPolicyInterface
{
    public function can(AccessRequest $req): bool;

    public function decide(AccessRequest $req, bool $withTrace = false): AccessDecision;
}
