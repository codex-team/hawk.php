<?php

declare(strict_types=1);

namespace Hawk\Transport;

use Hawk\Event;

/**
 * Interface TransportInterface
 *
 * @package Hawk\Transport
 */
interface TransportInterface
{
    public function send(Event $event): void;
}
