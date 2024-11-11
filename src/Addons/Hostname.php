<?php

declare(strict_types=1);

namespace Hawk\Addons;

/**
 * Class Hostname
 *
 * @package Hawk\Addons
 */
class Hostname implements AddonInterface
{
    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'env';
    }

    /**
     * @inheritDoc
     */
    public function resolve(): array
    {
        $hostname = gethostname();

        if ($hostname === false) {
            return [];
        }

        return ['hostname' => $hostname];
    }
}
