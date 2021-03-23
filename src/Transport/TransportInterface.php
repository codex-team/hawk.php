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
    /**
     * Returns URL that object must send an Event
     *
     * @return string
     */
    public function getUrl(): string;

    /**
     * Sends an Event
     *
     * @param Event $event
     *
     * @return mixed
     */
    public function send(Event $event);
}
