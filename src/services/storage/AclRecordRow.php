<?php

namespace illusiard\entity_acl\services\storage;

final class AclRecordRow
{
    public function __construct(
        public string $entity,
        public ?string $recordId,
        public int $ownerFlags,
        public int $groupFlags,
        public int $otherFlags,
        public ?int $ownerId,
        public ?int $groupId,
        public int $priority,
    ) {}
}