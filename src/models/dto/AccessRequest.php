<?php

namespace illusiard\entity_acl\models\dto;

final class AccessRequest
{
    public function __construct(
        public int $userId,
        public string $entity,
        public string $operation,
        public int|string|null $recordId = null,
        public array $context = [],
    ) {}
}
