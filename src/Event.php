<?php

declare(strict_types=1);

namespace Hawk;

/**
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
    private $accessToken = '';

    /**
     * Events payload corresponding to Hawk format
     *
     * @var EventPayload
     */
    private $eventPayload;

    /**
     * Event constructor.
     *
     * @param string       $accessToken
     * @param EventPayload $eventPayload
     */
    public function __construct(string $accessToken, EventPayload $eventPayload)
    {
        $this->accessToken = $accessToken;
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
    public function jsonSerialize()
    {
        return [
            'token'        => $this->accessToken,
            'catcherType'  => $this->catcherType,
            'payload'      => $this->getEventPayload()
        ];
    }
}
