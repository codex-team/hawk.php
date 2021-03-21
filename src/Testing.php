<?php

declare(strict_types=1);

namespace Hawk;

class Testing
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
        throw new \Exception("sdfdsf");
    }
}