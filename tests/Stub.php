<?php

declare(strict_types=1);

namespace Hawk\Tests;

use Hawk\Catcher;

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
//        Catcher::get()->catchEvent([
//            'context' => [
//                'header' => '123'
//            ],
//            'user' => [
//                'id' => 11
//            ]
//        ]);
        $a = 1 / 0;
    }
}
