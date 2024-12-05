<?php

declare(strict_types=1);

namespace Hawk\Transport;

use Hawk\Event;
use Hawk\Options;
use Hawk\Transport;

/**
 * Class GuzzleTransport
 *
 * @package Hawk\Transport
 */
class GuzzleTransport extends Transport
{
    /**
     * @var \GuzzleHttp\Client
     */
    private $client;

    /**
     * GuzzleTransport constructor.
     *
     * @param Options $options
     */
    public function __construct(Options $options)
    {
        parent::__construct($options);

        $this->client = new \GuzzleHttp\Client();
    }

    /**
     * @inheritDoc
     */
    protected function _send(Event $event): string
    {
        $response = $this->client->post($this->getUrl(), [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode($event, JSON_UNESCAPED_UNICODE),
            'timeout' => $this->getTimeout(),
        ]);

        return $response->getBody()->getContents();
    }
}
