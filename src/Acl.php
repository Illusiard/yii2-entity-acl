<?php

namespace illusiard\entity_acl;

final class Acl
{
    public const LIST   = 1;
    public const READ   = 2;
    public const CREATE = 4;
    public const UPDATE = 8;
    public const DELETE = 16;

    public static function opToMask(string $op): int
    {
        return match ($op) {
            'list'   => self::LIST,
            'read'   => self::READ,
            'create' => self::CREATE,
            'update' => self::UPDATE,
            'delete' => self::DELETE,
            default  => 0,
        };
    }
}
