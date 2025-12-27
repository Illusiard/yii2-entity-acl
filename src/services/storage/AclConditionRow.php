<?php

namespace illusiard\entity_acl\services\storage;

final class AclConditionRow
{
    public function __construct(
        public int $id,
        public string $entity,
        public ?string $recordId,
        public string $effect, // allow|deny
        public int $opsMask,
        public array $subject,
        public array $when,
        public bool $enabled,
        public int $priority,
        public ?string $comment,
    ) {}
}