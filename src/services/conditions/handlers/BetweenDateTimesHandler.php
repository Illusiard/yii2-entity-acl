<?php

namespace illusiard\entity_acl\services\conditions\handlers;

use DateTimeImmutable;
use DateTimeZone;
use Throwable;
use illusiard\entity_acl\models\dto\AccessRequest;
use illusiard\entity_acl\services\conditions\ConditionEngine;
use illusiard\entity_acl\services\conditions\ConditionHandlerInterface;

final class BetweenDateTimesHandler implements ConditionHandlerInterface
{
    public function supports(string $type): bool
    {
        return $type === 'betweenDateTimes';
    }

    public function evaluate(array $payload, AccessRequest $request, ConditionEngine $engine): bool
    {
        $fromRaw = (string)($payload['from'] ?? '');
        $toRaw = (string)($payload['to'] ?? '');

        if ($fromRaw === '' || $toRaw === '') {
            return false;
        }

        $tzName = $payload['tz'] ?? date_default_timezone_get();
        if (!is_string($tzName) || $tzName === '') {
            return false;
        }

        try {
            $timezone = new DateTimeZone($tzName);
        } catch (Throwable) {
            return false;
        }

        $from = $this->parseDateTime($fromRaw, $timezone);
        $to = $this->parseDateTime($toRaw, $timezone);

        if ($from === null || $to === null) {
            return false;
        }

        if ($from > $to) {
            return false;
        }

        try {
            $now = new DateTimeImmutable('now', $timezone);
        } catch (Throwable) {
            return false;
        }

        return $now >= $from && $now <= $to;
    }

    private function parseDateTime(string $value, DateTimeZone $timezone): ?DateTimeImmutable
    {
        $dateTime = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $value, $timezone);

        if ($dateTime === false) {
            return null;
        }

        $errors = DateTimeImmutable::getLastErrors();
        if (
            is_array($errors)
            && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)
        ) {
            return null;
        }

        return $dateTime;
    }
}
