<?php

declare(strict_types=1);

namespace Hawk\Addons;

/**
 * Interface Addon
 *
 * @package Hawk\Addons
 */
interface AddonInterface
{
    /**
     * Returns addon name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Returns addon extra data
     *
     * @return array
     */
    public function resolve(): array;
}
