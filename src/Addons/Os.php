<?php

declare(strict_types=1);

namespace Hawk\Addons;

class Os implements AddonInterface
{
    /**
     * @return array
     */
    public function resolve(): array
    {
        return [
            'name'           => php_uname('s'),
            'version'        => php_uname('r'),
            'build'          => php_uname('v'),
            'kernel_version' => php_uname('a'),
        ];
    }
}
