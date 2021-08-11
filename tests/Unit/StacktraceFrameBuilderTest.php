<?php

declare(strict_types=1);

namespace Hawk\Tests\Unit;

use Hawk\Serializer;
use Hawk\StacktraceFrameBuilder;
use PHPUnit\Framework\TestCase;

class StacktraceFrameBuilderTest extends TestCase
{
    public function testResultingStacktraceFrames(): void
    {
        $serializer = new Serializer();
        $fixture = new StacktraceFrameBuilder($serializer);

        $testCase = [
            'exception' => new \Exception(),
            'stackSize' => 11
        ];

        $stacktrace = $fixture->buildStack($testCase['exception']);
        $this->assertCount($testCase['stackSize'], $stacktrace);
    }
}
