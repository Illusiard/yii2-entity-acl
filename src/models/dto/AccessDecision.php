<?php

namespace illusiard\entity_acl\models\dto;

final class AccessDecision
{
    public function __construct(
        public bool $allowed,
        public string $rule = '', // 'base' | 'condition_allow' | 'condition_deny' | 'default_deny'
        public array $trace = [], // позже: причины/шаги
    ) {}
}
