<?php

namespace illusiard\entity_acl;

final class Acl
{
    public const LIST             = 1;
    public const READ             = 2;
    public const CREATE           = 4;
    public const UPDATE           = 8;
    public const DELETE           = 16;
    public const RESTORE          = 32;
    public const PERMANENT_DELETE = 64;

    public const OPERATION_LIST             = 'list';
    public const OPERATION_READ             = 'read';
    public const OPERATION_CREATE           = 'create';
    public const OPERATION_UPDATE           = 'update';
    public const OPERATION_DELETE           = 'delete';
    public const OPERATION_RESTORE          = 'restore';
    public const OPERATION_PERMANENT_DELETE = 'permanentDelete';
    public const OPERATION_UNKNOWN          = 'unknown';

    public const OPERATIONS = [
        self::OPERATION_LIST,
        self::OPERATION_READ,
        self::OPERATION_CREATE,
        self::OPERATION_UPDATE,
        self::OPERATION_DELETE,
        self::OPERATION_RESTORE,
        self::OPERATION_PERMANENT_DELETE,
    ];

    public static function operationToMask(string $operation): int
    {
        return match ($operation) {
            self::OPERATION_LIST             => self::LIST,
            self::OPERATION_READ             => self::READ,
            self::OPERATION_CREATE           => self::CREATE,
            self::OPERATION_UPDATE           => self::UPDATE,
            self::OPERATION_DELETE           => self::DELETE,
            self::OPERATION_RESTORE          => self::RESTORE,
            self::OPERATION_PERMANENT_DELETE => self::PERMANENT_DELETE,
            default                          => 0,
        };
    }
}
