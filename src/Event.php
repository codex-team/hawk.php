<?php

declare(strict_types=1);

namespace Hawk;

final class Event implements \JsonSerializable
{
    /**
     * @var string
     */
    private $catcherType = 'error/php';

    /**
     * @var EventPayload
     */
    private $eventPayload;

    /**
     * @return string
     */
    public function getCatcherType(): string
    {
        return $this->catcherType;
    }

    /**
     * @return EventPayload
     */
    public function getEventPayload(): EventPayload
    {
        return $this->eventPayload;
    }

    /**
     * @param EventPayload $eventPayload
     *
     * @return $this
     */
    public function setEventPayload(EventPayload $eventPayload): self
    {
        $this->eventPayload = $eventPayload;

        return $this;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'catcherType'  => $this->getCatcherType(),
            'eventPayload' => $this->getEventPayload()
        ];
    }
}
