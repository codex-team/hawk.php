<?php

declare(strict_types=1);

namespace Hawk\Tests\Unit;

use Hawk\EventPayload;
use Hawk\EventPayloadBuilder;
use Hawk\Serializer;
use Hawk\Severity;
use Hawk\StacktraceFrameBuilder;
use PHPUnit\Framework\TestCase;

class EventPayloadBuilderTest extends TestCase
{
    /**
     * @return array{0: EventPayloadBuilder, 1: \ReflectionMethod}
     */
    private function builderWithNormalizeBacktrace(): array
    {
        $serializer = new Serializer();
        $stack = new StacktraceFrameBuilder($serializer);
        $builder = new EventPayloadBuilder($stack);
        $m = new \ReflectionMethod(EventPayloadBuilder::class, 'normalizeBacktrace');
        $m->setAccessible(true);

        return [$builder, $m];
    }

    public function testNormalizeBacktraceDoesNotPutRawArgsIntoAdditionalData(): void
    {
        [$builder, $normalize] = $this->builderWithNormalizeBacktrace();

        $frame = [
            'file'     => '/fake/handler.php',
            'line'     => 7,
            'class'    => 'SomeErrorHandler',
            'type'     => '::',
            'function' => 'handle',
            'args'     => [256, 'message', __FILE__, 7, ['nested' => true]],
        ];

        $stack = $normalize->invoke($builder, [$frame]);

        $this->assertArrayNotHasKey('args', $stack[0]['additionalData'] ?? []);
        $this->assertNotEmpty($stack[0]['arguments'] ?? []);
    }

    public function testNormalizeBacktraceLimitsArgumentCount(): void
    {
        [$builder, $normalize] = $this->builderWithNormalizeBacktrace();
        $max = StacktraceFrameBuilder::MAX_FRAME_ARGUMENTS;

        $frame = [
            'file'     => '/x.php',
            'line'     => 1,
            'function' => 'not_registered_function_' . uniqid(),
            'args'     => array_values(range(1, $max + 5)),
        ];

        $stack = $normalize->invoke($builder, [$frame]);

        $this->assertCount($max, $stack[0]['arguments']);
    }

    public function testNormalizeBacktracePreservesPrebuiltArgumentLineWithoutDelimiter(): void
    {
        [$builder, $normalize] = $this->builderWithNormalizeBacktrace();
        $long = str_repeat('Z', 50_000);

        $frame = [
            'file'      => '/x.php',
            'line'      => 1,
            'function'  => 'f',
            'arguments' => [$long],
        ];

        $stack = $normalize->invoke($builder, [$frame]);
        $line  = $stack[0]['arguments'][0] ?? '';

        $this->assertSame($long, $line);
    }

    public function testNormalizeBacktraceTruncatesPrebuiltNameOnlyValuePreserved(): void
    {
        [$builder, $normalize] = $this->builderWithNormalizeBacktrace();
        $longName = str_repeat('K', StacktraceFrameBuilder::MAX_ARGUMENT_NAME_BYTES + 50);
        $longValue = str_repeat('V', 12_345);
        $prebuiltLine = $longName . ' = ' . $longValue;

        $frame = [
            'file'      => '/x.php',
            'line'      => 1,
            'function'  => 'f',
            'arguments' => [$prebuiltLine],
        ];

        $stack = $normalize->invoke($builder, [$frame]);
        $line  = $stack[0]['arguments'][0] ?? '';
        $parts = explode(' = ', $line, 2);
        $this->assertCount(2, $parts);
        [$namePart, $valuePart] = $parts;

        $this->assertLessThanOrEqual(StacktraceFrameBuilder::MAX_ARGUMENT_NAME_BYTES, strlen($namePart));
        $this->assertStringEndsWith('...', $namePart);
        $this->assertSame($longValue, $valuePart);
    }

    public function testNormalizeBacktraceFinishesWithGlobalsLikeNestingInAdditionalData(): void
    {
        [$builder, $normalize] = $this->builderWithNormalizeBacktrace();

        $deep = ['level' => []];
        $cur =& $deep['level'];
        for ($i = 0; $i < 50; $i++) {
            $cur['d'] = [];
            $cur =& $cur['d'];
        }
        $cur['leaf'] = 1;

        $frame = [
            'file'     => '/x.php',
            'line'     => 1,
            'function' => 'x',
            'class'    => 'C',
            'type'     => '->',
            'args'     => [1],
            'custom'   => $deep,
        ];

        $stack = $normalize->invoke($builder, [$frame]);
        $this->assertIsArray($stack[0]['additionalData']['custom'] ?? null);
    }

    public function testNormalizeBacktraceMarksCircularArraysInAdditionalData(): void
    {
        [$builder, $normalize] = $this->builderWithNormalizeBacktrace();

        $globalsLike = ['marker' => true];
        $globalsLike['GLOBALS'] = &$globalsLike;

        $frame = [
            'file'     => '/x.php',
            'line'     => 1,
            'function' => 'x',
            'args'     => [],
            'custom'   => $globalsLike,
        ];

        $stack = $normalize->invoke($builder, [$frame]);
        $custom = $stack[0]['additionalData']['custom'] ?? null;
        $this->assertIsArray($custom);
        $this->assertTrue($custom['marker']);
        $this->assertSame('[circular]', $custom['GLOBALS']);
    }

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
            'exception' => $exception,
            'type' => 1
        ]);

        $this->assertInstanceOf(EventPayload::class, $payload);
        $this->assertEmpty($payload->getContext());
        $this->assertEmpty($payload->getUser());
        $this->assertEquals($exception->getMessage(), $payload->getTitle());
        $this->assertEquals($payload->getType(), Severity::fatal());
    }
}
