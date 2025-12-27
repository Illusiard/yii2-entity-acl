<?php

namespace illusiard\entity_acl\services\conditions;

use illusiard\entity_acl\models\dto\AccessRequest;
use illusiard\entity_acl\services\storage\AclStorageInterface;
use illusiard\entity_acl\services\subject\SubjectResolverInterface;

final class ConditionEngine
{
    /** @var ConditionHandlerInterface[] */
    private array $handlers = [];

    public function __construct(
        private readonly AclStorageInterface $storage,
        private readonly SubjectResolverInterface $subjectResolver,
        array $handlers = []
    ) {
        foreach ($handlers as $h) {
            $this->addHandler($h);
        }
    }

    public function addHandler(ConditionHandlerInterface $handler): void
    {
        $this->handlers[] = $handler;
    }

    public function evaluateSubject(array $subject, AccessRequest $req): bool
    {
        foreach ($subject as $key => $value) {
            switch ($key) {
                case 'userId':
                case 'user_id':
                    if ((int)$req->userId !== (int)$value) {
                        return false;
                    }
                    break;

                case 'groupId':
                case 'group_id':
                    $gid = $this->subjectResolver->resolveGroupId($req->userId, $req->context);
                    if ($gid === null || (int)$gid !== (int)$value) {
                        return false;
                    }
                    break;

                case 'ownerId':
                case 'owner_id':
                    $oid = $this->subjectResolver->resolveOwnerId($req);
                    if ($oid === null || (int)$oid !== (int)$value) {
                        return false;
                    }
                    break;

                default:
                    //неизвестный subject-ключ
                    return false;
            }
        }

        return true;
    }

    /**
     * when может быть:
     * - [] => true
     * - [ {type: "...", ...}, {condition: [1,2]} ] => AND
     */
    public function evaluateWhen(array $when, AccessRequest $req, array &$trace = []): bool
    {
        foreach ($when as $item) {
            if (!is_array($item)) {
                return false;
            }

            // ссылка на другие conditions
            if (isset($item['condition']) && is_array($item['condition'])) {
                foreach ($item['condition'] as $condId) {
                    $condId = (int)$condId;
                    $cond   = $this->storage->findConditionById($condId);
                    if ($cond === null || !$cond->enabled) {
                        return false;
                    }

                    if (!$this->evaluateSubject($cond->subject, $req)) {
                        return false;
                    }
                    if (!$this->evaluateWhen($cond->when, $req, $trace)) {
                        return false;
                    }
                }
                continue;
            }

            $type = (string)($item['type'] ?? '');
            if ($type === '') {
                return false;
            }

            $handled = false;
            foreach ($this->handlers as $handler) {
                if ($handler->supports($type)) {
                    $handled = true;
                    if (!$handler->evaluate($item, $req, $this)) {
                        return false;
                    }
                    break;
                }
            }

            if (!$handled) {
                // неизвестный type => не срабатывает (false)
                return false;
            }
        }

        return true;
    }

    public function getStorage(): AclStorageInterface
    {
        return $this->storage;
    }

    public function getSubjectResolver(): SubjectResolverInterface
    {
        return $this->subjectResolver;
    }
}
