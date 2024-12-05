<?php

declare(strict_types=1);

namespace Hawk\Transport;

use Hawk\Event;
use Hawk\Options;
use Hawk\Transport;

/**
 * Class CurlTransport is a transport object
 *
 * @package Hawk\Transport
 */
class CurlTransport extends Transport
{
    /**
     * CurlTransport constructor.
     *
     * @param Options $options
     */
    public function __construct(Options $options)
    {
        parent::__construct($options);
    }

    /**
     * @inheritDoc
     */
    protected function _send(Event $event): string
    {
        /**
         * If php-curl is not available then throw an exception
         */
        if (!extension_loaded('curl')) {
            throw new \Exception('The cURL PHP extension is required to use the Hawk PHP Catcher');
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->getUrl());
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($event, JSON_UNESCAPED_UNICODE));
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->getTimeout());
        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }
}
