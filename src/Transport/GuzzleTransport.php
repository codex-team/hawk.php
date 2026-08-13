<?php

declare(strict_types=1);

namespace Hawk\Transport;

use Hawk\Event;

/**
 * Class GuzzleTransport
 *
 * @deprecated Use a custom TransportInterface implementation instead.
 *
 * @package Hawk\Transport
 */
class GuzzleTransport implements TransportInterface
{
    private $url;

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    /**
     * @inheritDoc
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @inheritDoc
     */
    public function send(Event $event): void
    {
        throw new \BadMethodCallException('GuzzleTransport is not implemented. Please provide your own TransportInterface implementation.');
    }
}
