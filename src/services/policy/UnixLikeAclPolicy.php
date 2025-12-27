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

    public function can(AccessRequest $req): bool
    {
        return $this->decide($req)->allowed;
    }

    public function decide(AccessRequest $req, bool $withTrace = false): AccessDecision
    {
        $trace = [];
        $opMask = Acl::opToMask($req->op);

        if ($opMask === 0) {
            return new AccessDecision(false, 'default_deny', $trace);
        }

        $recordId = $req->recordId !== null ? (string)$req->recordId : null;

        // 1) base ACL from bes_acl_record (record first, then entity)
        $acl = $this->engine->getStorage()->findAclRecord($req->entity, $recordId);
        $baseAllowed = false;

        if ($acl !== null) {
            $subjectGroupId = $this->engine->getSubjectResolver()->resolveGroupId($req->userId, $req->context);
            $subjectOwnerId = $this->engine->getSubjectResolver()->resolveOwnerId($req);

            $segment = 'other';
            $flags = $acl->otherFlags;

            if ($acl->ownerId !== null && $subjectOwnerId !== null && $acl->ownerId === $subjectOwnerId) {
                $segment = 'owner';
                $flags = $acl->ownerFlags;
            } elseif ($acl->groupId !== null && $subjectGroupId !== null && $acl->groupId === $subjectGroupId) {
                $segment = 'group';
                $flags = $acl->groupFlags;
            }

            $baseAllowed = (($flags & $opMask) !== 0);

            if ($withTrace) {
                $trace[] = [
                    'step' => 'base',
                    'segment' => $segment,
                    'flags' => $flags,
                    'opMask' => $opMask,
                    'allowed' => $baseAllowed,
                    'scope' => $acl->recordId !== null ? 'record' : 'entity',
                ];
            }
        } elseif ($withTrace) {
            $trace[] = ['step' => 'base', 'allowed' => false, 'scope' => 'none'];
        }

        // 2) conditions: deny first, then allow
        $conds = $this->engine->getStorage()->findConditions($req->entity, $recordId, $opMask);

        $denyMatched = false;
        $allowMatched = false;

        foreach ($conds as $cond) {
            // subject filter
            if (!$this->engine->evaluateSubject($cond->subject, $req)) {
                continue;
            }

            $localTrace = [];
            if (!$this->engine->evaluateWhen($cond->when, $req, $localTrace)) {
                continue;
            }

            if ($cond->effect === 'deny') {
                $denyMatched = true;
                if ($withTrace) {
                    $trace[] = ['step' => 'condition', 'id' => $cond->id, 'effect' => 'deny', 'matched' => true];
                }
                // deny wins сразу
                return new AccessDecision(false, 'condition_deny', $trace);
            }

            if ($cond->effect === 'allow') {
                $allowMatched = true;
                if ($withTrace) {
                    $trace[] = ['step' => 'condition', 'id' => $cond->id, 'effect' => 'allow', 'matched' => true];
                }
                // allow не возвращаем сразу — вдруг ниже будет deny (но мы deny уже обработали выше)
                // однако у нас deny checked first? мы в одном проходе; проще: продолжим, но deny приоритетом выше.
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
