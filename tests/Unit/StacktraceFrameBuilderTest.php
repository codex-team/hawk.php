<?php

declare(strict_types=1);

namespace Hawk\Tests\Unit;

use Hawk\Serializer;
use Hawk\StacktraceFrameBuilder;
use PHPUnit\Framework\TestCase;

class StacktraceFrameBuilderTest extends TestCase
{
    public function testTruncateUtf8StringToMaxBytesPreservesValidUtf8WhenCuttingMultibyte(): void
    {
        $ru = str_repeat('П', 200);
        $out = StacktraceFrameBuilder::truncateUtf8StringToMaxBytes($ru, 80);
        $this->assertLessThanOrEqual(80, strlen($out));
        $this->assertStringEndsWith('...', $out);
        $this->assertSame(1, preg_match('//u', $out), 'output must be valid UTF-8 for JSON');
        $this->assertNotFalse(json_encode($out, JSON_UNESCAPED_UNICODE));
    }

    public function testUtf8SafePrefixMaxBytesViaReflectionDoesNotSplitTwoByteChar(): void
    {
        $method = new \ReflectionMethod(StacktraceFrameBuilder::class, 'utf8SafePrefixMaxBytes');
        $method->setAccessible(true);
        // 'П' is U+041F, two bytes in UTF-8
        $this->assertSame('', $method->invoke(null, 'Президент', 1));
        $this->assertSame('П', $method->invoke(null, 'Президент', 2));
        $this->assertSame('Пр', $method->invoke(null, 'Президент', 4));
    }

    public function testResultingStacktraceFrames(): void
    {
        $serializer = new Serializer();
        $fixture = new StacktraceFrameBuilder($serializer);

        $testCase = [
            'exception' => new \Exception(),
            'stackSize' => 12
        ];

        $stacktrace = $fixture->buildStack($testCase['exception']);
        $this->assertCount($testCase['stackSize'], $stacktrace);
    }
}
