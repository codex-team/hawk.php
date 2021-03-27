<?php

declare(strict_types=1);

namespace Hawk;

/**
 * Class EventPayload keeps events information about occurred event
 *
 * @package Hawk
 */
final class EventPayload implements \JsonSerializable
{
    /**
     * Events title
     *
     * @var string
     */
    private $title = '';

    /**
     * Events error type
     *
     * @var int
     */
    private $type = 0;

    /**
     * Events description
     *
     * @var string
     */
    private $description = '';

    /**
     * Event occurrence timestamp
     *
     * @var int
     */
    private $timestamp = 0;

    /**
     * Events level
     *
     * @var int
     */
    private $level = 0;

    /**
     * Events stacktrace
     *
     * @var array
     */
    private $backtrace = [];

    /**
     * Environment specific data (OS, Runtime, Platform (Web, CLI))
     *
     * @var array
     */
    private $addons = [];

    /**
     * Application release
     *
     * @var string
     */
    private $release = '';

    /**
     * Occurred event on user
     *
     * @var array
     */
    private $user = [];

    /**
     * Application custom context
     *
     * @var array
     */
    private $context = [];

    /**
     * EventPayload constructor.
     *
     * @param array $payload
     */
    public function __construct(array $payload = [])
    {
        foreach ($payload as $prop => $value) {
            if (property_exists($this, $prop)) {
                $this->{$prop} = $value;
            }
        }
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     *
     * @return $this
     */
    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return $this
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    /**
     * @param int $timestamp
     *
     * @return $this
     */
    public function setTimestamp(int $timestamp): self
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @param int $level
     *
     * @return $this
     */
    public function setLevel(int $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getBacktrace(): array
    {
        return $this->backtrace;
    }

    public function setBacktrace(array $backtrace): self
    {
        $this->backtrace = $backtrace;

        return $this;
    }

    /**
     * @return array
     */
    public function getAddons(): array
    {
        return $this->addons;
    }

    /**
     * @param array $addons
     *
     * @return $this
     */
    public function setAddons(array $addons): self
    {
        $this->addons = $addons;

        return $this;
    }

    /**
     * @return string
     */
    public function getRelease(): string
    {
        return $this->release;
    }

    /**
     * @param string $release
     *
     * @return $this
     */
    public function setRelease(string $release): self
    {
        $this->release = $release;

        return $this;
    }

    /**
     * @return array
     */
    public function getUser(): array
    {
        return $this->user;
    }

    /**
     * @param array $user
     *
     * @return $this
     */
    public function setUser(array $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * @param array $context
     *
     * @return $this
     */
    public function setContext(array $context): self
    {
        $this->context = $context;

        return $this;
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * @return array
     */
    private function toArray(): array
    {
        return [
            'title'       => $this->getTitle(),
            'type'        => $this->getType(),
            'description' => $this->getDescription(),
            'timestamp'   => $this->getTimestamp(),
            'level'       => $this->getLevel(),
            'backtrace'   => $this->getBacktrace(),
            'addons'      => $this->getAddons(),
            'release'     => $this->getRelease(),
            'user'        => $this->getUser(),
            'context'     => $this->getContext()
        ];
    }
}
