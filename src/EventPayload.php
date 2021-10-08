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
     * Events error type (severity)
     *
     * @var ?Severity
     */
    private $type;

    /**
     * Events description
     *
     * @var string
     */
    private $description = '';

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
     * Returns event title
     *
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
     * Returns errors' type
     *
     * @return null|Severity
     */
    public function getType(): ?Severity
    {
        return $this->type;
    }

    /**
     * @param int $type
     *
     * @return $this
     */
    public function setType(int $severity): self
    {
        $this->type = Severity::fromError($severity);

        return $this;
    }

    /**
     * Returns errors' description
     *
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
     * Returns errors' backtrace
     *
     * @return array
     */
    public function getBacktrace(): array
    {
        return $this->backtrace;
    }

    /**
     * @param array $backtrace
     *
     * @return $this
     */
    public function setBacktrace(array $backtrace): self
    {
        $this->backtrace = $backtrace;

        return $this;
    }

    /**
     * Returns event addons
     *
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
     * Returns release version
     *
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
     * Returns user, if passed on event
     *
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
     * Returns event context (any additional data)
     *
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
            'type'        => $this->getType() ? $this->getType()->getValue() : '',
            'description' => $this->getDescription(),
            'backtrace'   => $this->getBacktrace(),
            'addons'      => $this->getAddons(),
            'release'     => $this->getRelease(),
            'user'        => $this->getUser(),
            'context'     => $this->getContext()
        ];
    }
}
