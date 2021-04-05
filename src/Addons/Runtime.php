<?php

declare(strict_types=1);

namespace Hawk\Addons;

/**
 * Class Runtime
 *
 * @package Hawk\Addons
 */
class Runtime implements AddonInterface
{
    /**
     * @inheritDoc
     */
    public function resolve(): array
    {
        return [
            'name'    => 'php',
            'version' => \PHP_VERSION
        ];
    }
}
