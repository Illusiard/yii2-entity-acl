<?php

namespace illusiard\entity_acl\tests\unit\conditions;

use illusiard\entity_acl\models\dto\AccessRequest;
use illusiard\entity_acl\services\conditions\ConditionEngine;
use illusiard\entity_acl\services\conditions\handlers\BetweenDateTimesHandler;
use illusiard\entity_acl\services\subject\ContextSubjectResolver;
use illusiard\entity_acl\tests\_support\FakeAclStorage;
use PHPUnit\Framework\TestCase;

class BetweenDateTimesHandlerTest extends TestCase
{
    private function makeEngine(): ConditionEngine
    {
        return new ConditionEngine(
            new FakeAclStorage(),
            new ContextSubjectResolver(),
            [new BetweenDateTimesHandler()]
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

    public function testMatchesInsideRange(): void
    {
        $engine = $this->makeEngine();
        $req = $this->makeRequest();

        $result = $engine->evaluateWhen([
            [
                'type' => 'betweenDateTimes',
                'from' => '2000-01-01 00:00:00',
                'to' => '2100-01-01 00:00:00',
                'tz' => 'Europe/Amsterdam',
            ],
        ], $req);

        $this->assertTrue($result);
    }

    public function testDoesNotMatchOutsideRange(): void
    {
        $engine = $this->makeEngine();
        $req = $this->makeRequest();

        $result = $engine->evaluateWhen([
            [
                'type' => 'betweenDateTimes',
                'from' => '2000-01-01 00:00:00',
                'to' => '2001-01-01 00:00:00',
                'tz' => 'Europe/Amsterdam',
            ],
        ], $req);

        $this->assertFalse($result);
    }

    public function testReturnsFalseForInvalidDateFormat(): void
    {
        $engine = $this->makeEngine();
        $req = $this->makeRequest();

        $result = $engine->evaluateWhen([
            [
                'type' => 'betweenDateTimes',
                'from' => '2025-12-10T09:00:00',
                'to' => '2026-01-11 18:00:00',
                'tz' => 'Europe/Amsterdam',
            ],
        ], $req);

        $this->assertFalse($result);
    }
}
