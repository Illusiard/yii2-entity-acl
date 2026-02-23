<?php

namespace illusiard\entity_acl\tests\unit;

use illusiard\entity_acl\Acl;
use illusiard\entity_acl\models\dto\AccessRequest;
use illusiard\entity_acl\services\conditions\ConditionEngine;
use illusiard\entity_acl\services\policy\UnixLikeAclPolicy;
use illusiard\entity_acl\services\storage\AclConditionRow;
use illusiard\entity_acl\services\subject\ContextSubjectResolver;
use illusiard\entity_acl\tests\_support\FakeAclStorage;
use PHPUnit\Framework\TestCase;

class UnixLikeAclPolicyOwnerSubjectTest extends TestCase
{
    private function makePolicy(FakeAclStorage $storage): UnixLikeAclPolicy
    {
        $resolver = new ContextSubjectResolver();

        $engine = new ConditionEngine(
            $storage,
            $resolver,
            []
        );

        return new UnixLikeAclPolicy($engine);
    }

    public function testAllowConditionMatchesByOwnerIdFromResolver(): void
    {
        $storage = new FakeAclStorage();

        /**
         * Условие:
         *  allow read post
         *  если ownerId == 42
         */
        $storage->setConditions('post', null, [
            new AclConditionRow(
                id: 1,
                entity: 'post',
                recordId: null,
                effect: 'allow',
                opsMask: Acl::READ,
                subject: [
                    'ownerId' => 42,
                ],
                when: [],
                enabled: true,
                priority: 10,
                comment: null
            ),
        ]);

        $policy = $this->makePolicy($storage);

        // ownerId приходит НЕ напрямую в subject,
        // а через resolver -> context['ownerId']
        $reqAllowed = new AccessRequest(
            userId   : 10,
            entity   : 'post',
            operation: 'read',
            recordId : '100',
            context  : [
                'ownerId' => 42,
            ]
        );

        $reqDenied = new AccessRequest(
            userId   : 10,
            entity   : 'post',
            operation: 'read',
            recordId : '101',
            context  : [
                'ownerId' => 99,
            ]
        );

        $this->assertTrue(
            $policy->can($reqAllowed),
            'Access must be allowed when ownerId matches subject condition'
        );

        $this->assertFalse(
            $policy->can($reqDenied),
            'Access must be denied when ownerId does not match subject condition'
        );
    }
}
