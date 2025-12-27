<?php

namespace illusiard\entity_acl\services\conditions\handlers;

use illusiard\entity_acl\services\conditions\ConditionEngine;
use illusiard\entity_acl\services\conditions\ConditionHandlerInterface;
use illusiard\entity_acl\models\dto\AccessRequest;

final class TimeWindowHandler implements ConditionHandlerInterface
{
    public function supports(string $type): bool
    {
        return $type === 'betweenHours';
    }

    public function evaluate(array $payload, AccessRequest $req, ConditionEngine $engine): bool
    {
        $from = (int)($payload['from'] ?? 0);
        $to   = (int)($payload['to'] ?? 24);

        // timezone можно передавать в context, иначе PHP default
        $tz = $req->context['timezone'] ?? null;
        if (is_string($tz)) {
            $old = date_default_timezone_get();
            date_default_timezone_set($tz);
        }

        $h = (int)date('G'); // 0..23

        if (isset($old)) {
            date_default_timezone_set($old);
        }

        // интервал [from, to), без поддержки wrap-around пока
        return $h >= $from && $h < $to;
    }
}
