<?php

namespace illusiard\entity_acl\tests\unit;

use illusiard\entity_acl\Acl;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AclTest extends TestCase
{
    /**
     * @return array<string, array{string, int}>
     */
    public static function newOperationProvider(): array
    {
        return [
            'restore' => ['restore', Acl::RESTORE],
            'permanentDelete' => ['permanentDelete', Acl::PERMANENT_DELETE],
        ];
    }

    #[DataProvider('newOperationProvider')]
    public function testNewOperationsMapToMasks(string $operation, int $expectedMask): void
    {
        $this->assertSame($expectedMask, Acl::operationToMask($operation));
        $this->assertContains($operation, Acl::OPERATIONS);
    }
}
