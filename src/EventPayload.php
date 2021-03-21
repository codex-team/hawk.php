<?php

declare(strict_types=1);

namespace Hawk;

final class EventPayload implements \JsonSerializable
{
    /**
     * Events title
     *
     * @var string
     */
    private $title = '';

    /**
     * @todo descripbe PHP types
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
     * Event occurence timestamp
     *
     * @var int
     */
    private $timestamp;

    /**
     * Events level
     *
     * @var int
     */
    private $level;

    /**
     * Events stacktrace
     *
     * @var Backtrace[]
     */
    private $backtrace;

    /**
     * HTTP Request GET params
     *
     * @var array
     */
    private $getParams = [];

    /**
     * HTTP Request POST params
     *
     * @var array
     */
    private $postParams = [];

    /**
     * HTTP Request headers
     *
     * @var array
     */
    private $headers = [];

    /**
     * @var array
     */
    private $addons = [];

    /**
     * @var array
     */
    private $release = [];

    /**
     * @var array
     */
    private $user = [];

    /**
     * @var array
     */
    private $context = [];

    /**
     * EventPayload constructor.
     *
     * @param array $payload
     */
    public function __construct(array $payload)
    {
        foreach ($payload as $prop => $value) {
            if (property_exists($this, $prop)) {
                $this->{$prop} = $value;
            }
        }
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return [
            'title'       => $this->title,
            'type'        => $this->type,
            'description' => $this->description,
            'timestamp'   => $this->timestamp,
            'level'       => $this->level,
            'backtrace'   => $this->backtrace,
            'get'         => $this->getParams,
            'post'        => $this->postParams,
            'headers'     => $this->headers,
            'addons'      => $this->addons,
            'release'     => $this->release,
            'user'        => $this->user,
            'context'     => $this->context
        ];
    }
}
