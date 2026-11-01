<?php

declare(strict_types=1);

namespace Hawk\Tests\Unit;

use Hawk\EventPayloadBuilder;
use Hawk\Handler;
use Hawk\Options;
use Hawk\Serializer;
use Hawk\StacktraceFrameBuilder;
use Hawk\Transport\TransportInterface;
use PHPUnit\Framework\TestCase;

class HandlerFailureTest extends TestCase
{
    public function testTransportFailureDuringDeprecationDoesNotInterruptApplication(): void
    {
        $transport = $this->createMock(TransportInterface::class);
        $transport->expects($this->once())
            ->method('send')
            ->willThrowException(new \RuntimeException('Hawk rate limit'));

        $handler = $this->createHandler($transport);
        $previousHandlerCalled = false;
        $previousReporting = error_reporting(E_ALL);
        $registered = false;

        set_error_handler(function () use (&$previousHandlerCalled): bool {
            $previousHandlerCalled = true;

            return true;
        });

        try {
            $handler->registerErrorHandler();
            $registered = true;
            trigger_error('deprecated behavior', E_USER_DEPRECATED);

            $this->assertTrue($previousHandlerCalled);
        } finally {
            if ($registered) {
                restore_error_handler();
            }
            restore_error_handler();
            error_reporting($previousReporting);
        }
    }

    public function testTransportFailureDoesNotReplaceOriginalException(): void
    {
        $transport = $this->createMock(TransportInterface::class);
        $transport->method('send')
            ->willThrowException(new \RuntimeException('Hawk unavailable'));

        $handler = $this->createHandler($transport);
        $original = new \RuntimeException('Application failure');

        try {
            $handler->handleException($original);
            $this->fail('The original exception should be rethrown.');
        } catch (\RuntimeException $exception) {
            $this->assertSame($original, $exception);
        }
    }

    public function testFailureInPreviousErrorHandlerStillReachesApplication(): void
    {
        $transport = $this->createMock(TransportInterface::class);
        $transport->method('send')
            ->willThrowException(new \RuntimeException('Hawk unavailable'));

        $handler = $this->createHandler($transport);
        $previousFailure = new \RuntimeException('Application error handler failed');
        $previousReporting = error_reporting(E_ALL);
        $registered = false;

        set_error_handler(function () use ($previousFailure): void {
            throw $previousFailure;
        });

        try {
            $handler->registerErrorHandler();
            $registered = true;

            try {
                trigger_error('deprecated behavior', E_USER_DEPRECATED);
                $this->fail('The previous handler exception should be rethrown.');
            } catch (\RuntimeException $exception) {
                $this->assertSame($previousFailure, $exception);
            }
        } finally {
            if ($registered) {
                restore_error_handler();
            }
            restore_error_handler();
            error_reporting($previousReporting);
        }
    }

    public function testTransportFailureStillInvokesPreviousExceptionHandler(): void
    {
        $transport = $this->createMock(TransportInterface::class);
        $transport->method('send')
            ->willThrowException(new \RuntimeException('Hawk unavailable'));

        $handler = $this->createHandler($transport);
        $original = new \RuntimeException('Application failure');
        $handledException = null;
        $registered = false;

        set_exception_handler(function (\Throwable $exception) use (&$handledException): void {
            $handledException = $exception;
        });

        try {
            $handler->registerExceptionHandler();
            $registered = true;
            $handler->handleException($original);

            $this->assertSame($original, $handledException);
        } finally {
            if ($registered) {
                restore_exception_handler();
            }
            restore_exception_handler();
        }
    }

    public function testSendEventCanRecoverAfterTransportFailure(): void
    {
        $transport = $this->createMock(TransportInterface::class);
        $transport->expects($this->exactly(2))
            ->method('send')
            ->willThrowException(new \RuntimeException('Hawk unavailable'));

        $handler = $this->createHandler($transport);
        $handler->sendEvent(['title' => 'first']);
        $handler->sendEvent(['title' => 'second']);
    }

    public function testReportingFailureCannotRecursivelyReportItself(): void
    {
        $transport = $this->createMock(TransportInterface::class);
        $handler = $this->createHandler($transport);

        $transport->expects($this->once())
            ->method('send')
            ->willReturnCallback(function () use ($handler): void {
                $handler->sendEvent(['title' => 'nested']);
            });

        $handler->sendEvent(['title' => 'outer']);
    }

    public function testBeforeSendCannotRecursivelyCapture(): void
    {
        $transport = $this->createMock(TransportInterface::class);
        $transport->expects($this->once())->method('send');

        $handler = null;
        $beforeSendCalls = 0;
        $handler = $this->createHandler($transport, [
            'beforeSend' => function ($event) use (&$handler, &$beforeSendCalls) {
                ++$beforeSendCalls;
                $handler->sendEvent(['title' => 'nested']);

                return $event;
            }
        ]);

        $handler->sendEvent(['title' => 'outer']);
        $this->assertSame(1, $beforeSendCalls);
    }

    public function testBeforeSendCannotRecursivelyCaptureException(): void
    {
        $transport = $this->createMock(TransportInterface::class);
        $transport->expects($this->once())->method('send');

        $handler = null;
        $handler = $this->createHandler($transport, [
            'beforeSend' => function ($event) use (&$handler) {
                $handler->handleException(new \RuntimeException('Nested exception'));

                return $event;
            }
        ]);

        $handler->sendEvent(['title' => 'outer']);
    }

    public function testNestedExceptionPreservesPreviousExceptionHandler(): void
    {
        $transport = $this->createMock(TransportInterface::class);
        $transport->expects($this->exactly(2))->method('send');

        $handler = null;
        $handler = $this->createHandler($transport, [
            'beforeSend' => function ($event) use (&$handler) {
                $handler->handleException(new \RuntimeException('Nested exception'));

                return $event;
            }
        ]);

        $previousHandled = [];
        $registered = false;
        set_exception_handler(function (\Throwable $exception) use (&$previousHandled): void {
            $previousHandled[] = $exception;
        });

        try {
            $handler->registerExceptionHandler();
            $registered = true;
            $handler->sendEvent(['title' => 'outer']);
            $this->assertSame([], $previousHandled);

            $later = new \RuntimeException('Later exception');
            $handler->handleException($later);
            $this->assertSame([$later], $previousHandled);
        } finally {
            if ($registered) {
                restore_exception_handler();
            }
            restore_exception_handler();
        }
    }

    public function testBeforeSendFailureFromManualCaptureIsNotHidden(): void
    {
        $transport = $this->createMock(TransportInterface::class);
        $transport->expects($this->never())->method('send');

        $failure = new \RuntimeException('Invalid event data');
        $handler = $this->createHandler($transport, [
            'beforeSend' => function () use ($failure): void {
                throw $failure;
            }
        ]);

        $this->expectExceptionObject($failure);
        $handler->sendEvent(['title' => 'event']);
    }

    public function testBeforeSendFailureFromAutomaticCaptureIsSuppressed(): void
    {
        $transport = $this->createMock(TransportInterface::class);
        $transport->expects($this->never())->method('send');

        $handler = $this->createHandler($transport, [
            'beforeSend' => function (): void {
                throw new \RuntimeException('Invalid event data');
            }
        ]);

        $previousReporting = error_reporting(E_ALL);

        try {
            $this->assertFalse($handler->handleError(E_USER_DEPRECATED, 'deprecated behavior', __FILE__, __LINE__));
        } finally {
            error_reporting($previousReporting);
        }
    }

    public function testTransportErrorDoesNotInterruptApplication(): void
    {
        $transport = $this->createMock(TransportInterface::class);
        $transport->expects($this->once())
            ->method('send')
            ->willThrowException(new \TypeError('Invalid transport response'));

        $handler = $this->createHandler($transport);
        $handler->sendEvent(['title' => 'event']);
    }

    private function createHandler(TransportInterface $transport, array $options = []): Handler
    {
        return new Handler(
            new Options($options + ['integrationToken' => 'test-token']),
            $transport,
            new EventPayloadBuilder(new StacktraceFrameBuilder(new Serializer()))
        );
    }
}
