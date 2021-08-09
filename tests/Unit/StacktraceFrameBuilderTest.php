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
        $exception = new \Exception();

        $stacktrace = $fixture->buildStack($exception);
        $this->assertCount(11, $stacktrace);
    }
}
