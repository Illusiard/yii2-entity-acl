<?php

namespace illusiard\entity_acl\services\conditions\handlers;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Throwable;
use illusiard\entity_acl\services\conditions\ConditionEngine;
use illusiard\entity_acl\services\conditions\ConditionHandlerInterface;
use illusiard\entity_acl\models\dto\AccessRequest;

final class TimeWindowHandler implements ConditionHandlerInterface
{
    public function supports(string $type): bool
    {
        return $type === 'betweenHours';
    }

    public function evaluate(array $payload, AccessRequest $request, ConditionEngine $engine): bool
    {
        $from = (int)($payload['from'] ?? 0);
        $to   = (int)($payload['to'] ?? 24);

        if ($from < 0 || $from > 23 || $to < 1 || $to > 24 || $from >= $to) {
            return false;
        }

        $timezone = $this->resolveTimezone($payload['tz'] ?? $request->context['timezone'] ?? null);
        if ($timezone === null) {
            return false;
        }

        $now = $this->resolveNow($payload['now'] ?? null, $timezone);
        $h = (int)$now->format('G'); // 0..23

        // интервал [from, to), без поддержки wrap-around пока
        return $h >= $from && $h < $to;
    }

    private function resolveTimezone(mixed $value): ?DateTimeZone
    {
        $timezone = is_string($value) && $value !== '' ? $value : date_default_timezone_get();

        try {
            return new DateTimeZone($timezone);
        } catch (Throwable) {
            return null;
        }
    }

    private function resolveNow(mixed $value, DateTimeZone $timezone): DateTimeImmutable
    {
        if ($value instanceof DateTimeInterface) {
            return DateTimeImmutable::createFromInterface($value)->setTimezone($timezone);
        }

        return new DateTimeImmutable('now', $timezone);
    }
}
