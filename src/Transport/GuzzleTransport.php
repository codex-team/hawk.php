<?php

declare(strict_types=1);

namespace Hawk\Transport;

use Hawk\Event;

class GuzzleTransport implements TransportInterface
{
    private $guzzle;

    public function __construct()
    {
//        $this->guzzle = ...
    }

    public function send(Event $event): void
    {
        // TODO: Implement send() method.
    }
}