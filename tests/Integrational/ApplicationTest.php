<?php

declare(strict_types=1);

namespace Hawk\Tests\Integrational;

use PHPUnit\Framework\TestCase;

class ApplicationTest extends TestCase
{
    /**
     * @var Application
     */
    private $application;

    protected function setUp(): void
    {
        parent::setUp();

        $this->application = new Application();
        $this->application->run();
    }

    public function testExecutionWithoutError(): void
    {
        $this->application->doSafeWork();
        $this->assertTrue(true);
    }

    public function testExecutionWithException(): void
    {
        $this->application->doWorkWithException(1);
    }

    public function testExecutionWithError(): void
    {
        $this->application->doUnsafeWork();
    }
}