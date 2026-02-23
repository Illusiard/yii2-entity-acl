<?php

namespace illusiard\entity_acl\tests\unit\conditions;

use DateTimeImmutable;
use illusiard\entity_acl\models\dto\AccessRequest;
use illusiard\entity_acl\services\conditions\ConditionEngine;
use illusiard\entity_acl\services\conditions\handlers\WeekdayInHandler;
use illusiard\entity_acl\services\subject\ContextSubjectResolver;
use illusiard\entity_acl\tests\_support\FakeAclStorage;
use PHPUnit\Framework\TestCase;

class WeekdayInHandlerTest extends TestCase
{
    private function makeEngine(): ConditionEngine
    {
        return new ConditionEngine(
            new FakeAclStorage(),
            new ContextSubjectResolver(),
            [new WeekdayInHandler()]
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

    public function testMatchesWhenCurrentIsoWeekdayIsInDaysList(): void
    {
        $engine = $this->makeEngine();
        $req = $this->makeRequest();

        $result = $engine->evaluateWhen([
            [
                'type' => 'weekdayIn',
                'days' => [1, 2, 3, 4, 5],
                'now' => new DateTimeImmutable('2026-02-23 10:00:00'),
            ],
        ], $req);

        $this->assertTrue($result);
    }

    public function testDoesNotMatchWhenCurrentIsoWeekdayIsNotInDaysList(): void
    {
        $engine = $this->makeEngine();
        $req = $this->makeRequest();

        $result = $engine->evaluateWhen([
            [
                'type' => 'weekdayIn',
                'days' => [6, 7],
                'now' => new DateTimeImmutable('2026-02-23 10:00:00'),
            ],
        ], $req);

        $this->assertFalse($result);
    }

    public function testReturnsFalseForInvalidPayload(): void
    {
        $engine = $this->makeEngine();
        $req = $this->makeRequest();

        $result = $engine->evaluateWhen([
            [
                'type' => 'weekdayIn',
                'now' => new DateTimeImmutable('2026-02-23 10:00:00'),
            ],
        ], $req);

        $this->assertFalse($result);
    }
}
