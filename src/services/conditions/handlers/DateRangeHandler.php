<?php

namespace illusiard\entity_acl\services\conditions\handlers;

use DateTimeImmutable;
use illusiard\entity_acl\services\conditions\ConditionEngine;
use illusiard\entity_acl\services\conditions\ConditionHandlerInterface;
use illusiard\entity_acl\models\dto\AccessRequest;

final class DateRangeHandler implements ConditionHandlerInterface
{
    public function supports(string $type): bool
    {
        return $type === 'betweenDates';
    }

    public function evaluate(array $payload, AccessRequest $req, ConditionEngine $engine): bool
    {
        $from = (string)($payload['from'] ?? '');
        $to   = (string)($payload['to'] ?? '');

        if ($from === '' || $to === '') {
            return false;
        }

        // ожидаем YYYY-MM-DD
        $now = new DateTimeImmutable('now');

        $dFrom = DateTimeImmutable::createFromFormat('Y-m-d', $from);
        $dTo   = DateTimeImmutable::createFromFormat('Y-m-d', $to);

        if (!$dFrom || !$dTo) {
            return false;
        }

        // [from, to] включительно
        return $now >= $dFrom && $now <= $dTo;
    }
}
