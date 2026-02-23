<?php

namespace illusiard\entity_acl\tests\unit;

use illusiard\entity_acl\Acl;
use illusiard\entity_acl\AclService;
use illusiard\entity_acl\services\conditions\ConditionEngine;
use illusiard\entity_acl\services\integration\EntityAclAdapter;
use illusiard\entity_acl\services\policy\UnixLikeAclPolicy;
use illusiard\entity_acl\services\storage\AclRecordRow;
use illusiard\entity_acl\services\subject\ContextSubjectResolver;
use illusiard\entity_acl\tests\_support\FakeAclStorage;
use PHPUnit\Framework\TestCase;

class EntityAclAdapterTest extends TestCase
{
    public function testSmokeReadAndUpdate(): void
    {
        $storage = new FakeAclStorage();
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

        $engine = new ConditionEngine($storage, new ContextSubjectResolver(), []);
        $policy = new UnixLikeAclPolicy($engine);
        AclService::setInstance(new AclService($policy));

        $adapter = new EntityAclAdapter();

        $this->assertTrue($adapter->canRead(1, 'post', 10));
        $this->assertFalse($adapter->canUpdate(1, 'post', 10));
    }
}
