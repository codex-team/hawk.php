<?php

declare(strict_types=1);

namespace Hawk\Addons;

/**
 * Class Environment
 *
 * @package Hawk\Addons
 */
class Environment implements AddonInterface
{
    private array $environment = [];

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'environment';
    }

    /**
     * @inheritDoc
     */
    public function resolve(): array
    {
        $this->addHostname();

        return $this->environment;
    }

    private function addHostname(): void
    {
        $hostname = gethostname();

        if ($hostname !== false) {
            $this->environment['hostname'] = $hostname;
        }
    }
}
