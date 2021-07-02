<?php

declare(strict_types=1);

namespace Hawk\Tests\Integrational;

use Hawk\Catcher;

class Application
{
    /**
     * Initializes some application
     */
    public function run(): void
    {
        Catcher::init([
            'release' => 'hash',
            'integrationToken' => '123321',
        ]);
    }

    /**
     * Do something that works fine
     */
    public function doSafeWork(): void
    {
        // Safe execution
    }

    /**
     * Do something that throws exception
     *
     * @param $value
     *
     * @throws \Exception
     */
    public function doWorkWithException($value): void
    {
        throw new \Exception('error');
    }

    /**
     * Do something unsafe
     *
     * @return int
     */
    public function doUnsafeWork(): int
    {
        return 1 / 0;
    }
}