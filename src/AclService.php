<?php

namespace illusiard\entity_acl;

use illusiard\entity_acl\services\conditions\ConditionEngine;
use illusiard\entity_acl\services\policy\AccessPolicyInterface;
use illusiard\entity_acl\services\storage\AclStorageInterface;

final class AclService
{
    private static ?self $instance = null;

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
            throw new \RuntimeException('AclService is not initialized. Add AclBootstrap and module config.');
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
}