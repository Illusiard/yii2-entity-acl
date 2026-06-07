<?php

namespace illusiard\entity_acl\tests\unit\conditions;

use DateTimeImmutable;
use illusiard\entity_acl\models\dto\AccessRequest;
use illusiard\entity_acl\services\conditions\ConditionEngine;
use illusiard\entity_acl\services\conditions\handlers\DateRangeHandler;
use illusiard\entity_acl\services\subject\ContextSubjectResolver;
use illusiard\entity_acl\tests\_support\FakeAclStorage;
use PHPUnit\Framework\TestCase;

class DateRangeHandlerTest extends TestCase
{
    private function makeEngine(): ConditionEngine
    {
        return new ConditionEngine(
            new FakeAclStorage(),
            new ContextSubjectResolver(),
            [new DateRangeHandler()]
        );
    }

    private function makeRequest(): AccessRequest
    {
        return new AccessRequest(
            userId   : 1,
            entity   : 'post',
            operation: 'read',
            recordId : '1',
            context  : []
        );
    }

    public function testMatchesInclusiveDateRange(): void
    {
        $engine = $this->makeEngine();
        $req = $this->makeRequest();

        $result = $engine->evaluateWhen([
            [
                'type' => 'betweenDates',
                'from' => '2026-02-23',
                'to' => '2026-02-23',
                'now' => new DateTimeImmutable('2026-02-23 23:59:59'),
            ],
        ], $req);

        $this->assertTrue($result);
    }

    public function testReturnsFalseForInvalidDate(): void
    {
        $engine = $this->makeEngine();
        $req = $this->makeRequest();

        $result = $engine->evaluateWhen([
            [
                'type' => 'betweenDates',
                'from' => '2026-02-31',
                'to' => '2026-03-01',
            ],
        ], $req);

        $this->assertFalse($result);
    }
}
