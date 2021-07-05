<?php

declare(strict_types=1);

namespace Hawk\Tests\Unit;

use Hawk\EventPayload;
use Hawk\EventPayloadFactory;
use PHPUnit\Framework\TestCase;

class EventPayloadFactoryTest extends TestCase
{
    public function testCreationWithDefaultStacktrace(): void
    {
        $context = [
            'special'
        ];

        $user = [
            'id' => 1,
            'name' => 'Tester'
        ];

        $factory = new EventPayloadFactory();
        $payload = $factory->create([
            'context' => $context,
            'user' => $user,
        ]);

        $this->assertInstanceOf(EventPayload::class, $payload);
        $this->assertSame($user, $payload->getUser());
        $this->assertSame($context, $payload->getContext());
    }

    public function testCreationWithCustomStacktrace(): void
    {
        $exception = new \Exception('exception message');

        $factory = new EventPayloadFactory();
        $payload = $factory->create([
            'context' => [],
            'user' => [],
            'exception' => $exception
        ]);

        $this->assertInstanceOf(EventPayload::class, $payload);
        $this->assertEmpty($payload->getContext());
        $this->assertEmpty($payload->getUser());
        $this->assertEquals($exception->getMessage(), $payload->getTitle());
    }
}
