<?php

declare(strict_types=1);

namespace Hawk\Tests\Unit;

use PHPUnit\Framework\TestCase;

class FatalHandlerTest extends TestCase
{
    public function testFatalErrorIsReportedDuringShutdown(): void
    {
        list($exitCode, $output) = $this->runFixture('fatal');

        $this->assertNotSame(0, $exitCode);
        $this->assertSame(1, substr_count($output, "HAWK_EVENT\n"));
    }

    public function testNonFatalErrorIsNotReportedDuringShutdown(): void
    {
        list($exitCode, $output) = $this->runFixture('warning');

        $this->assertSame(0, $exitCode);
        $this->assertSame(0, substr_count($output, "HAWK_EVENT\n"));
    }

    public function testUncaughtExceptionIsNotReportedTwice(): void
    {
        list($exitCode, $output) = $this->runFixture('exception');

        $this->assertNotSame(0, $exitCode);
        $this->assertSame(1, substr_count($output, "HAWK_EVENT\n"));
    }

    private function runFixture(string $scenario): array
    {
        if (!function_exists('proc_open')) {
            $this->markTestSkipped('proc_open is required to test shutdown errors.');
        }

        $command = escapeshellarg(PHP_BINARY)
            . ' -d display_errors=stderr -d log_errors=0 '
            . escapeshellarg(__DIR__ . '/../Fixtures/fatal-handler.php')
            . ' ' . escapeshellarg($scenario);
        $process = proc_open($command, [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ], $pipes);

        $this->assertIsResource($process);

        $output = stream_get_contents($pipes[1]);
        stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        return [proc_close($process), $output];
    }
}
