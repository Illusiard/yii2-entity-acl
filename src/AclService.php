<?php

namespace illusiard\entity_acl;

use illusiard\entity_acl\services\conditions\ConditionEngine;
use illusiard\entity_acl\services\integration\EntityAclAdapter;
use illusiard\entity_acl\services\integration\EntityAclAdapterInterface;
use illusiard\entity_acl\services\policy\AccessPolicyInterface;
use illusiard\entity_acl\services\storage\AclStorageInterface;
use RuntimeException;

final class AclService
{
    private static ?self $instance = null;
    private ?EntityAclAdapterInterface $entityAdapter = null;

    public function __construct(
        private readonly AccessPolicyInterface $policy
    ) {
    }

    public static function setInstance(self $svc): void
    {
        self::$instance = $svc;
    }

    public static function instance(): self
    {
        if (!self::$instance) {
            throw new RuntimeException('AclService is not initialized. Add AclBootstrap and module config.');
        }

        return self::$instance;
    }

    public function getPolicy(): AccessPolicyInterface
    {
        return $this->policy;
    }

    public function getStorage(): AclStorageInterface
    {
        return $this->policy->getEngine()->getStorage();
    }

    public function getEngine(): ConditionEngine
    {
        return $this->policy->getEngine();
    }

    public function entityAdapter(): EntityAclAdapterInterface
    {
        if ($this->entityAdapter === null) {
            $this->entityAdapter = new EntityAclAdapter();
        }

        return $this->entityAdapter;
    }
}
