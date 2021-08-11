<?php

declare(strict_types=1);

namespace Hawk\Tests\Unit;

use Hawk\EventPayload;
use Hawk\EventPayloadBuilder;
use Hawk\Serializer;
use Hawk\StacktraceFrameBuilder;
use PHPUnit\Framework\TestCase;

class EventPayloadBuilderTest extends TestCase
{
    public function testCreationWithDefaultException(): void
    {
        $context = [
            'special'
        ];

        $user = [
            'id' => 1,
            'name' => 'Tester'
        ];

        $serializer = new Serializer();
        $stacktraceFrameBuilder = new StacktraceFrameBuilder($serializer);
        $eventPayloadBuilder = new EventPayloadBuilder($stacktraceFrameBuilder);
        $payload = $eventPayloadBuilder->create([
            'context' => $context,
            'user' => $user,
        ]);

        $this->assertInstanceOf(EventPayload::class, $payload);
        $this->assertSame($user, $payload->getUser());
        $this->assertSame($context, $payload->getContext());
    }

    public function testCreationWithCustomException(): void
    {
        $exception = new \Exception('exception message');

        $serializer = new Serializer();
        $stacktraceFrameBuilder = new StacktraceFrameBuilder($serializer);

        $eventPayloadBuilder = new EventPayloadBuilder($stacktraceFrameBuilder);
        $payload = $eventPayloadBuilder->create([
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
