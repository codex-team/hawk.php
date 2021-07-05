<?php

declare(strict_types=1);

namespace Hawk\Tests\Unit\Addons;

use Hawk\Addons\Runtime;
use PHPUnit\Framework\TestCase;

class RuntimeTest extends TestCase
{
    public function testRuntimeAddonFields(): void
    {
        $runtime = new Runtime();
        $result = $runtime->resolve();

        $expectedFields = [
            'name',
            'version'
        ];

        $this->assertSame($expectedFields, array_keys($result));
    }
}
