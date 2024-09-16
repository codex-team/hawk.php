<?php

declare(strict_types=1);

namespace Hawk;

/**
 * This value-object class used to keep complete Event data to send
 *
 * @package Hawk
 */
final class Event implements \JsonSerializable
{
    /**
     * @var string
     */
    private $catcherType = 'errors/php';

    /**
     * @var string
     */
    private $integrationToken = '';

    /**
     * Events payload corresponding to Hawk format
     *
     * @var EventPayload
     */
    private $eventPayload;

    /**
     * Event constructor.
     *
     * @param string       $integrationToken
     * @param EventPayload $eventPayload
     */
    public function __construct(string $integrationToken, EventPayload $eventPayload)
    {
        $this->integrationToken = $integrationToken;
        $this->eventPayload = $eventPayload;
    }

    /**
     * Returns event payload
     *
     * @return EventPayload
     */
    public function getEventPayload(): EventPayload
    {
        return $this->eventPayload;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'token'        => $this->integrationToken,
            'catcherType'  => $this->catcherType,
            'payload'      => $this->getEventPayload()->jsonSerialize()
        ];
    }
}
