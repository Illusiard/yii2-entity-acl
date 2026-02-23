<?php

namespace illusiard\entity_acl;

use illusiard\entity_acl\services\conditions\ConditionEngine;
use illusiard\entity_acl\services\conditions\handlers\AlwaysHandler;
use illusiard\entity_acl\services\conditions\handlers\DateRangeHandler;
use illusiard\entity_acl\services\conditions\handlers\TimeWindowHandler;
use illusiard\entity_acl\services\conditions\handlers\WeekdayInHandler;
use illusiard\entity_acl\services\policy\UnixLikeAclPolicy;
use illusiard\entity_acl\services\storage\DbAclStorage;
use illusiard\entity_acl\services\subject\ContextSubjectResolver;
use yii\base\BootstrapInterface;

final class Bootstrap implements BootstrapInterface
{
    public function bootstrap($app): void
    {
        if (!$app->hasModule('entity-acl')) {
            return;
        }

        /** @var Module $module */
        $module = $app->getModule('entity-acl');

        $storage = new DbAclStorage();

        $resolver = $module->config['groupResolver'] ?? new ContextSubjectResolver();

        $engine = new ConditionEngine($storage, $resolver, [
            new AlwaysHandler(),
            new TimeWindowHandler(),
            new DateRangeHandler(),
            new WeekdayInHandler(),
        ]);

        $policy = new UnixLikeAclPolicy($engine);

        AclService::setInstance(new AclService($policy));
    }
}
