<?php

declare(strict_types=1);

namespace Hawk\Transport;

use Hawk\Event;

/**
 * Class GuzzleTransport
 *
 * @package Hawk\Transport
 */
class GuzzleTransport implements TransportInterface
{
    private $guzzle;
    private $url;

    public function __construct(string $url)
    {
//        $this->guzzle = ...
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
        // TODO: Implement send() method.
    }
}
