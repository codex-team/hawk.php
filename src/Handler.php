<?php

declare(strict_types=1);

namespace Hawk;

use ErrorException;
use Hawk\Transport\TransportInterface;
use Hawk\Util\Stacktrace;
use Throwable;

/**
 * Class Handler
 *
 * @package Hawk
 */
class Handler
{
    /**
     * @var TransportInterface
     */
    private $transport;

    /**
     * Project access token. Generated on https://hawk.so
     *
     * @var string
     */
    private $accessToken;

    /**
     * Handler constructor.
     *
     * @param TransportInterface $transport
     * @param string             $accessToken
     */
    public function __construct(TransportInterface $transport, string $accessToken)
    {
        $this->transport = $transport;
        $this->accessToken = $accessToken;
    }

    /**
     * Method to send any event to Hawk
     *
     * @param array $payload
     */
    public function catchEvent(array $payload): void
    {
        $event = new Event(
            $this->accessToken,
            new EventPayload($payload)
        );

        $this->send($event);
    }

    /**
     * Process exception and sent to Hawk
     *
     * @param Throwable $exception
     * @param array     $context   array of data to be passed with event
     */
    public function catchException(Throwable $exception, array $context = []): void
    {
        $payload = [
            'title'     => $exception->getMessage(),
            'context'   => $context,
            'backtrace' => Stacktrace::buildStack($exception)
        ];

        $event = new Event(
            $this->accessToken,
            new EventPayload($payload)
        );

        $this->send($event);
    }

    /**
     * Catches error and sends to the Hawk
     *
     * @param string $message
     * @param string $file
     * @param int    $code
     * @param int    $line
     * @param array  $context
     *
     * @return bool
     */
    public function catchError(string $message, string $file, int $code, int $line, array $context = []): void
    {
        $payload = [
            'title'   => $message,
            'context' => $context
        ];

        $exception = new ErrorException($message, $code, 0, $file, $line);
        $payload['backtrace'] = Stacktrace::buildStack($exception);

        $event = new Event(
            $this->accessToken,
            new EventPayload($payload)
        );

        $this->send($event);
    }

    /**
     * Fatal errors catch method
     * Being called on script exit
     *
     * @return bool|null
     */
    public function catchFatal(): void
    {
        $error = error_get_last();
        $payload = [
            'title' => $error['message']
        ];

        $exception = new ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);
        $payload['backtrace'] = Stacktrace::buildStack($exception);

        $event = new Event(
            $this->accessToken,
            new EventPayload($payload)
        );
        $this->send($event);
    }

    /**
     * Send event to Hawk
     *
     * @param Event $event
     */
    private function send(Event $event): void
    {
        $this->transport->send($event);
    }
}
