<?php

namespace illusiard\entity_acl\services\conditions\handlers;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Throwable;
use illusiard\entity_acl\services\conditions\ConditionEngine;
use illusiard\entity_acl\services\conditions\ConditionHandlerInterface;
use illusiard\entity_acl\models\dto\AccessRequest;

final class DateRangeHandler implements ConditionHandlerInterface
{
    public function supports(string $type): bool
    {
        return $type === 'betweenDates';
    }

    public function evaluate(array $payload, AccessRequest $request, ConditionEngine $engine): bool
    {
        $from = (string)($payload['from'] ?? '');
        $to   = (string)($payload['to'] ?? '');

        if ($from === '' || $to === '') {
            return false;
        }

        $timezone = $this->resolveTimezone($payload['tz'] ?? $request->context['timezone'] ?? null);
        if ($timezone === null) {
            return false;
        }

        $dFrom = $this->parseDate($from, $timezone);
        $dTo   = $this->parseDate($to, $timezone);

        if ($dFrom === null || $dTo === null || $dFrom > $dTo) {
            return false;
        }

        $now = $this->resolveNow($payload['now'] ?? null, $timezone);
        $today = $now->setTime(0, 0);

        // [from, to] включительно
        return $today >= $dFrom && $today <= $dTo;
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

    private function parseDate(string $value, DateTimeZone $timezone): ?DateTimeImmutable
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value, $timezone);

        if ($date === false) {
            return null;
        }

        $errors = DateTimeImmutable::getLastErrors();
        if (
            is_array($errors)
            && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)
        ) {
            return null;
        }

        return $date;
    }
}
