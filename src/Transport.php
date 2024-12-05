<?php

declare(strict_types=1);

namespace Hawk;

use Hawk\Exception\TransportException;
use Hawk\Transport\CurlTransport;
use Hawk\Transport\GuzzleTransport;

/**
 * Interface TransportInterface
 *
 * @package Hawk\Transport
 */
class Transport
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @var int
     */
    protected $timeout;

    public static function init(Options $options)
    {
        $transports = self::getTransports();

        if (!array_key_exists($options->getTransport(), $transports)) {
            throw new TransportException('Invalid transport specified');
        }

        return new $transports[$options->getTransport()]($options);
    }

    public static function getTransports(): array
    {
        return [
            'curl' => CurlTransport::class,
            'guzzle' => GuzzleTransport::class,
        ];
    }

    public function __construct(Options $options)
    {
        $this->url = $options->getUrl();
        $this->timeout = $options->getTimeout();
    }

    /**
     * Returns URL that object must send an Event
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * Sends an Event
     *
     * @param Event $event
     *
     * @return mixed
     */
    public function send(Event $event)
    {
        $response = $this->_send($event);

        try {
            $data = json_decode($response, true);
        } catch (\Exception $e) {
            $data = null;
        }

        return $data;
    }

    protected function _send(Event $event): string
    {
        throw new TransportException('Not implemented transport method _send()');
    }
}
