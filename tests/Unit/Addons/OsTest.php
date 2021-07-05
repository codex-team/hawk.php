<?php

declare(strict_types=1);

namespace Hawk\Tests\Unit\Addons;

use Hawk\Addons\Os;
use PHPUnit\Framework\TestCase;

class OsTest extends TestCase
{
    public function testOsAddonFields(): void
    {
        $os = new Os();
        $result = $os->resolve();
        $expectedFields = [
            'name',
            'version',
            'build',
            'kernel_version'
        ];

        $this->assertSame($expectedFields, array_keys($result));
    }
}
