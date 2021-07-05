<?php

declare(strict_types=1);

namespace Hawk;

/**
 * Class Options is responsible for configuring catcher
 *
 * @package Hawk
 */
class Options
{
    /**
     * Default available options
     *
     * @var array
     */
    private $options = [
        'integrationToken' => '',
        'url'              => 'https://k1.hawk.so/',
        'release'          => '',
        'error_types'      => \E_ALL,
        'beforeSend'       => null
    ];

    /**
     * Options constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Returns access token. It is available on projects settings
     *
     * @return string
     */
    public function getIntegrationToken(): string
    {
        return $this->options['integrationToken'];
    }

    /**
     * Returns URL that should be send
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->options['url'];
    }

    /**
     * Returns application release
     *
     * @return string
     */
    public function getRelease(): string
    {
        return $this->options['release'];
    }

    /**
     * Returns error types
     *
     * @return int
     */
    public function getErrorTypes(): int
    {
        return $this->options['error_types'];
    }

    /**
     * Returns before send callback
     *
     * @return callable|null
     */
    public function getBeforeSend(): ?callable
    {
        return $this->options['beforeSend'];
    }
}
