<?php

namespace illusiard\entity_acl\tests\unit\conditions;

use illusiard\entity_acl\models\dto\AccessRequest;
use illusiard\entity_acl\services\conditions\ConditionEngine;
use illusiard\entity_acl\services\conditions\handlers\ConditionRefHandler;
use illusiard\entity_acl\services\storage\AclConditionRow;
use illusiard\entity_acl\services\subject\ContextSubjectResolver;
use illusiard\entity_acl\tests\_support\FakeAclStorage;
use PHPUnit\Framework\TestCase;

class ConditionRefCycleTest extends TestCase
{
    public function testCycleBetweenConditionsReturnsFalse(): void
    {
        $storage = new FakeAclStorage();

        $storage->setConditions('post', null, [
            new AclConditionRow(
                id: 1,
                entity: 'post',
                recordId: null,
                effect: 'allow',
                opsMask: 1,
                subject: [],
                when: [
                    ['type' => 'conditionRef', 'id' => 2],
                ],
                enabled: true,
                priority: 10,
                comment: null
            ),
            new AclConditionRow(
                id: 2,
                entity: 'post',
                recordId: null,
                effect: 'allow',
                opsMask: 1,
                subject: [],
                when: [
                    ['type' => 'conditionRef', 'id' => 1],
                ],
                enabled: true,
                priority: 9,
                comment: null
            ),
        ]);

        $engine = new ConditionEngine(
            $storage,
            new ContextSubjectResolver(),
            [new ConditionRefHandler()]
        );

        $req = new AccessRequest(
            userId   : 1,
            entity   : 'post',
            operation: 'read',
            recordId : '1',
            context  : []
        );

        $this->assertFalse($engine->evaluateWhen([
            ['type' => 'conditionRef', 'id' => 1],
        ], $req));
    }
}
