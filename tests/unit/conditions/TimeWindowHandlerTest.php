<?php

namespace illusiard\entity_acl\tests\unit\conditions;

use DateTimeImmutable;
use illusiard\entity_acl\models\dto\AccessRequest;
use illusiard\entity_acl\services\conditions\ConditionEngine;
use illusiard\entity_acl\services\conditions\handlers\TimeWindowHandler;
use illusiard\entity_acl\services\subject\ContextSubjectResolver;
use illusiard\entity_acl\tests\_support\FakeAclStorage;
use PHPUnit\Framework\TestCase;

class TimeWindowHandlerTest extends TestCase
{
    private function makeEngine(): ConditionEngine
    {
        return new ConditionEngine(
            new FakeAclStorage(),
            new ContextSubjectResolver(),
            [new TimeWindowHandler()]
        );
    }

    private function makeRequest(): AccessRequest
    {
        return new AccessRequest(
            userId   : 1,
            entity   : 'post',
            operation: 'read',
            recordId : '1',
            context  : [
                'timezone' => 'Europe/Moscow',
            ]
        );
    }

    public function testMatchesInsideWindowWithoutMutatingDefaultTimezone(): void
    {
        $oldTimezone = date_default_timezone_get();
        $engine = $this->makeEngine();
        $req = $this->makeRequest();

        $result = $engine->evaluateWhen([
            [
                'type' => 'betweenHours',
                'from' => 9,
                'to' => 18,
                'now' => new DateTimeImmutable('2026-02-23 10:00:00 Europe/Moscow'),
            ],
        ], $req);

        $this->assertTrue($result);
        $this->assertSame($oldTimezone, date_default_timezone_get());
    }

    public function testReturnsFalseForInvalidTimezone(): void
    {
        $engine = $this->makeEngine();
        $req = new AccessRequest(
            userId   : 1,
            entity   : 'post',
            operation: 'read',
            recordId : '1',
            context  : [
                'timezone' => 'bad timezone',
            ]
        );

        $result = $engine->evaluateWhen([
            [
                'type' => 'betweenHours',
                'from' => 9,
                'to' => 18,
            ],
        ], $req);

        $this->assertFalse($result);
    }
}
