<?php

declare(strict_types=1);

namespace Hawk\Tests\Unit\Addons;

use Hawk\Addons\Headers;
use PHPUnit\Framework\TestCase;

class HeadersTest extends TestCase
{
    public function testHeaderAddonsFields(): void
    {
        $headers = new Headers();
        $result = $headers->resolve();

        $expectedFields = [
            'DOCUMENT_ROOT',
            'REMOTE_ADDR',
            'REMOTE_PORT',
            'SERVER_PROTOCOL',
            'SERVER_NAME',
            'SERVER_PORT',
            'HTTP_CONNECTION',
            'HTTP_CACHE_CONTROL',
            'HTTP_USER_AGENT',
            'HTTP_ACCEPT',
            'QUERY_STRING'
        ];

        $this->assertSame($expectedFields, array_keys($result));
    }
}
