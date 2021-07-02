<?php

declare(strict_types=1);

namespace Hawk\Tests\Unit\Monolog;

use Hawk\Catcher;
use Hawk\Monolog\Handler;
use PHPUnit\Framework\TestCase;

class HandlerTest extends TestCase
{
    public function testHandlerWithoutInitialization(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Catcher is not initialized');

        $handler = new Handler();
        $handler->write([]);
    }
}