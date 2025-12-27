<?php

namespace illusiard\entity_acl\tests\unit;

use illusiard\entity_acl\Acl;
use illusiard\entity_acl\models\dto\AccessRequest;
use illusiard\entity_acl\services\conditions\ConditionEngine;
use illusiard\entity_acl\services\policy\UnixLikeAclPolicy;
use illusiard\entity_acl\services\storage\AclConditionRow;
use illusiard\entity_acl\services\storage\AclRecordRow;
use illusiard\entity_acl\services\subject\ContextSubjectResolver;
use illusiard\entity_acl\tests\_support\FakeAclStorage;
use PHPUnit\Framework\TestCase;

class UnixLikeAclPolicyTest extends TestCase
{
    private function makePolicy(FakeAclStorage $storage): UnixLikeAclPolicy
    {
        $resolver = new ContextSubjectResolver();
        return new UnixLikeAclPolicy(new ConditionEngine($storage, $resolver, []));
    }

    public function testDefaultDenyWhenNoAclAndNoConditions(): void
    {
        $storage = new FakeAclStorage();
        $policy = $this->makePolicy($storage);

        $req = new AccessRequest(
            userId: 1,
            entity: 'post',
            op: 'read',
            recordId: '10',
            context: []
        );

        $this->assertFalse($policy->can($req));
    }

    public function testBaseAllowOtherSegment(): void
    {
        $storage = new FakeAclStorage();

        // entity-wide ACL: other can read
        $storage->setAcl('post', null, new AclRecordRow(
            entity: 'post',
            recordId: null,
            ownerFlags: 0,
            groupFlags: 0,
            otherFlags: Acl::READ,
            ownerId: null,
            groupId: null,
            priority: 0
        ));

        $policy = $this->makePolicy($storage);

        $req = new AccessRequest(
            userId: 1,
            entity: 'post',
            op: 'read',
            recordId: '10',
            context: []
        );

        $this->assertTrue($policy->can($req));
    }

    public function testConditionDenyOverridesBaseAllow(): void
    {
        $storage = new FakeAclStorage();

        // base allow read
        $storage->setAcl('post', null, new AclRecordRow(
            entity: 'post',
            recordId: null,
            ownerFlags: 0,
            groupFlags: 0,
            otherFlags: Acl::READ,
            ownerId: null,
            groupId: null,
            priority: 0
        ));

        // condition deny read (matches because subject/when empty)
        $storage->setConditions('post', null, [
            new AclConditionRow(
                id: 1,
                entity: 'post',
                recordId: null,
                effect: 'deny',
                opsMask: Acl::READ,
                subject: [],
                when: [],
                enabled: true,
                priority: 10,
                comment: null
            )
        ]);

        $policy = $this->makePolicy($storage);

        $req = new AccessRequest(
            userId: 1,
            entity: 'post',
            op: 'read',
            recordId: '10',
            context: []
        );

        $this->assertFalse($policy->can($req));
    }

    public function testConditionAllowGrantsWhenBaseDenies(): void
    {
        $storage = new FakeAclStorage();

        // base deny (no ACL record) -> default deny
        // allow condition for read
        $storage->setConditions('post', null, [
            new AclConditionRow(
                id: 2,
                entity: 'post',
                recordId: null,
                effect: 'allow',
                opsMask: Acl::READ,
                subject: [],
                when: [],
                enabled: true,
                priority: 10,
                comment: null
            )
        ]);

        $policy = $this->makePolicy($storage);

        $req = new AccessRequest(
            userId: 1,
            entity: 'post',
            op: 'read',
            recordId: '10',
            context: []
        );

        $this->assertTrue($policy->can($req));
    }

    public function testRecordLevelAclOverridesEntityLevel(): void
    {
        $storage = new FakeAclStorage();

        // entity-wide deny (explicit 0)
        $storage->setAcl('post', null, new AclRecordRow(
            entity: 'post',
            recordId: null,
            ownerFlags: 0,
            groupFlags: 0,
            otherFlags: 0,
            ownerId: null,
            groupId: null,
            priority: 0
        ));

        // record-level allow read for record 10
        $storage->setAcl('post', '10', new AclRecordRow(
            entity: 'post',
            recordId: '10',
            ownerFlags: 0,
            groupFlags: 0,
            otherFlags: Acl::READ,
            ownerId: null,
            groupId: null,
            priority: 0
        ));

        $policy = $this->makePolicy($storage);

        $reqAllow = new AccessRequest(
            userId: 1,
            entity: 'post',
            op: 'read',
            recordId: '10',
            context: []
        );

        $reqDeny = new AccessRequest(
            userId: 1,
            entity: 'post',
            op: 'read',
            recordId: '11',
            context: []
        );

        $this->assertTrue($policy->can($reqAllow));
        $this->assertFalse($policy->can($reqDeny));
    }

    public function testDenyWinsEvenIfAllowHasHigherPriority(): void
    {
        $storage = new FakeAclStorage();

        // base deny (none)
        // conditions: allow priority 100, deny priority 10 -> deny должен победить
        $storage->setConditions('post', null, [
            new AclConditionRow(
                id: 100,
                entity: 'post',
                recordId: null,
                effect: 'allow',
                opsMask: Acl::READ,
                subject: [],
                when: [],
                enabled: true,
                priority: 100,
                comment: null
            ),
            new AclConditionRow(
                id: 10,
                entity: 'post',
                recordId: null,
                effect: 'deny',
                opsMask: Acl::READ,
                subject: [],
                when: [],
                enabled: true,
                priority: 10,
                comment: null
            ),
        ]);

        $policy = $this->makePolicy($storage);

        $req = new AccessRequest(
            userId: 1,
            entity: 'post',
            op: 'read',
            recordId: '10',
            context: []
        );

        $this->assertFalse($policy->can($req));
    }

    public function testRecordLevelConditionDenyBeatsEntityLevelAllow(): void
    {
        $storage = new FakeAclStorage();

        // entity-level allow
        $storage->setConditions('post', null, [
            new AclConditionRow(
                id: 1,
                entity: 'post',
                recordId: null,
                effect: 'allow',
                opsMask: Acl::READ,
                subject: [],
                when: [],
                enabled: true,
                priority: 10,
                comment: null
            ),
        ]);

        // record-level deny for record 10
        $storage->setConditions('post', '10', [
            new AclConditionRow(
                id: 2,
                entity: 'post',
                recordId: '10',
                effect: 'deny',
                opsMask: Acl::READ,
                subject: [],
                when: [],
                enabled: true,
                priority: 10,
                comment: null
            ),
        ]);

        $policy = $this->makePolicy($storage);

        $req10 = new AccessRequest(
            userId: 1,
            entity: 'post',
            op: 'read',
            recordId: '10',
            context: []
        );

        $req11 = new AccessRequest(
            userId: 1,
            entity: 'post',
            op: 'read',
            recordId: '11',
            context: []
        );

        $this->assertFalse($policy->can($req10));
        $this->assertTrue($policy->can($req11));
    }
}
