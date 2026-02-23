<?php

namespace illusiard\entity_acl;

final class Acl
{
    public const LIST   = 1;
    public const READ   = 2;
    public const CREATE = 4;
    public const UPDATE = 8;
    public const DELETE = 16;

    public const OPERATION_LIST    = 'list';
    public const OPERATION_READ    = 'read';
    public const OPERATION_CREATE  = 'create';
    public const OPERATION_UPDATE  = 'update';
    public const OPERATION_DELETE  = 'delete';
    public const OPERATION_UNKNOWN = 'unknown';

    public static function opToMask(string $op): int
    {
        return match ($op) {
            self::OPERATION_LIST   => self::LIST,
            self::OPERATION_READ   => self::READ,
            self::OPERATION_CREATE => self::CREATE,
            self::OPERATION_UPDATE => self::UPDATE,
            self::OPERATION_DELETE => self::DELETE,
            default                => 0,
        };
    }
}
