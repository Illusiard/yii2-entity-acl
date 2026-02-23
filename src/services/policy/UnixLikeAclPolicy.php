<?php

namespace illusiard\entity_acl\services\policy;

use illusiard\entity_acl\Acl;
use illusiard\entity_acl\services\conditions\ConditionEngine;
use illusiard\entity_acl\models\dto\AccessDecision;
use illusiard\entity_acl\models\dto\AccessRequest;

/**
 * Реализация политики уровня управления доступом (ACL) в стиле Unix.
 *
 * Эта политика эмулирует традиционные права доступа к файлам Unix, расшаривая эти правила на сущности. С поддержкой:
 * - Владелец, группа и другие права доступа.
 * - Дополнительные условия с эффектами разрешения/запрета.
 * - Иерархический поиск ACL (для конкретной записи, а затем для всей сущности)
 *
 * Порядок принятия решения следующий:
 * 1. Проверить базовое правило — владелец > группа > другое.
 * 2. Оценить условия по порядку: условия отказа имеют наивысший приоритет.
 * 3. Разрешить из условий переопределения.
 * 4. Базовое правило разрешает, если не соблюдено ни одно условие.
 * 5. Отказ по умолчанию
 *
 * @package illusiard\entity_acl\services\policy
 */
final class UnixLikeAclPolicy implements AccessPolicyInterface
{
    public function __construct(
        private readonly ConditionEngine $engine,
    ) {}

    public function can(AccessRequest $request): bool
    {
        return $this->decide($request)->allowed;
    }

    public function decide(AccessRequest $request, bool $withTrace = false): AccessDecision
    {
        $trace = [];
        $operationMask = Acl::operationToMask($request->operation);

        if ($operationMask === 0) {
            return new AccessDecision(false, 'default_deny', $trace);
        }

        $recordId = $request->recordId !== null ? (string)$request->recordId : null;

        // 1) base ACL from bes_acl_record (record first, then entity)
        $acl = $this->engine->getStorage()->findAclRecord($request->entity, $recordId);
        $baseAllowed = false;

        if ($acl !== null) {
            $subjectGroupId = $this->engine->getSubjectResolver()->resolveGroupId($request->userId, $request->context);
            $subjectOwnerId = $this->engine->getSubjectResolver()->resolveOwnerId($request);

            $segment = 'other';
            $flags = $acl->otherFlags;

            if ($acl->ownerId !== null && $subjectOwnerId !== null && $acl->ownerId === $subjectOwnerId) {
                $segment = 'owner';
                $flags = $acl->ownerFlags;
            } elseif ($acl->groupId !== null && $subjectGroupId !== null && $acl->groupId === $subjectGroupId) {
                $segment = 'group';
                $flags = $acl->groupFlags;
            }

            $baseAllowed = (($flags & $operationMask) !== 0);

            if ($withTrace) {
                $trace[] = [
                    'step' => 'base',
                    'segment' => $segment,
                    'flags' => $flags,
                    'opMask' => $operationMask,
                    'allowed' => $baseAllowed,
                    'scope' => $acl->recordId !== null ? 'record' : 'entity',
                ];
            }
        } elseif ($withTrace) {
            $trace[] = ['step' => 'base', 'allowed' => false, 'scope' => 'none'];
        }

        // 2) conditions: deny first, then allow
        $conditions = $this->engine->getStorage()->findConditions($request->entity, $recordId, $operationMask);

        $denyMatched = false;
        $allowMatched = false;

        foreach ($conditions as $condition) {
            // subject filter
            if (!$this->engine->evaluateSubject($condition->subject, $request)) {
                continue;
            }

            $localTrace = [];
            if (!$this->engine->evaluateWhen($condition->when, $request, $localTrace)) {
                continue;
            }

            if ($condition->effect === 'deny') {
                $denyMatched = true;
                if ($withTrace) {
                    $trace[] = ['step' => 'condition', 'id' => $condition->id, 'effect' => 'deny', 'matched' => true];
                }
                //todo condition priority?..
                return new AccessDecision(false, 'condition_deny', $trace);
            }

            if ($condition->effect === 'allow') {
                $allowMatched = true;
                if ($withTrace) {
                    $trace[] = ['step' => 'condition', 'id' => $condition->id, 'effect' => 'allow', 'matched' => true];
                }
            }
        }

        // 3) итог
        if ($allowMatched) {
            return new AccessDecision(true, 'condition_allow', $trace);
        }

        if ($baseAllowed) {
            return new AccessDecision(true, 'base', $trace);
        }

        return new AccessDecision(false, 'default_deny', $trace);
    }

    public function getEngine(): ConditionEngine
    {
        return $this->engine;
    }
}
