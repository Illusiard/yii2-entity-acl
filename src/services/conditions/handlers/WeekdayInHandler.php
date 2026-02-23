<?php

namespace illusiard\entity_acl\services\conditions\handlers;

use DateTimeImmutable;
use DateTimeInterface;
use illusiard\entity_acl\models\dto\AccessRequest;
use illusiard\entity_acl\services\conditions\ConditionEngine;
use illusiard\entity_acl\services\conditions\ConditionHandlerInterface;

final class WeekdayInHandler implements ConditionHandlerInterface
{
    public function supports(string $type): bool
    {
        return $type === 'weekdayIn';
    }

    public function evaluate(array $payload, AccessRequest $request, ConditionEngine $engine): bool
    {
        if (!isset($payload['days']) || !is_array($payload['days'])) {
            return false;
        }

        $nowPayload = $payload['now'] ?? null;

        $now = $nowPayload instanceof DateTimeInterface
            ? DateTimeImmutable::createFromInterface($nowPayload)
            : new DateTimeImmutable('now');

        $currentDay = (int)$now->format('N');

        foreach ($payload['days'] as $day) {
            if ((int)$day === $currentDay) {
                return true;
            }
        }

        return false;
    }
}
