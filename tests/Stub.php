<?php

declare(strict_types=1);

namespace Hawk\Tests;

class Stub
{
    public function test()
    {
        $this->foo();
    }

    private function foo()
    {
        $this->bar();
    }

    private function bar()
    {
        throw new \Exception('sdfdsf');
    }
}
