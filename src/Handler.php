<?php

declare(strict_types=1);

namespace Hawk;

use ErrorException;
use Hawk\Transport\TransportInterface;
use Throwable;

/**
 * Class Handler is responsible for enabling and handling any occurred errors on application
 *
 * @package Hawk
 */
final class Handler
{
    /**
     * Options object
     *
     * @var Options
     */
    private $options;

    /**
     * Transport object
     *
     * @var TransportInterface
     */
    private $transport;

    /**
     * Events payload factory object
     *
     * @var EventPayloadFactory
     */
    private $eventPayloadFactory;

    /**
     * Handler constructor.
     *
     * @param Options             $options
     * @param TransportInterface  $transport
     * @param EventPayloadFactory $eventPayloadFactory
     */
    public function __construct(
        Options $options,
        TransportInterface $transport,
        EventPayloadFactory $eventPayloadFactory
    ) {
        $this->options = $options;
        $this->transport = $transport;
        $this->eventPayloadFactory = $eventPayloadFactory;
    }

    /**
     * Method to send manually any event to Hawk
     *
     * @param array $payload
     */
    public function catchEvent(array $payload): void
    {
        $eventPayload = $this->eventPayloadFactory->create($payload);
        $event = $this->prepareEvent($eventPayload);

        $this->send($event);
    }

    /**
     * Process exception and send to Hawk
     *
     * @param Throwable $exception
     * @param array     $context   array of data to be passed with event
     */
    public function catchException(Throwable $exception, array $context = []): void
    {
        $payload = [
            'exception' => $exception,
            'context'   => $context,
        ];

        $eventPayload = $this->eventPayloadFactory->create($payload);
        $event = $this->prepareEvent($eventPayload);

        $this->send($event);
    }

    /**
     * Catches error and sends to Hawk
     *
     * @param int    $level
     * @param string $message
     * @param string $file
     * @param int    $line
     */
    public function catchError(int $level, string $message, string $file, int $line): void
    {
        $exception = new ErrorException($message, $level, 0, $file, $line);
        $payload = [
            'exception' => $exception
        ];

        $eventPayload = $this->eventPayloadFactory->create($payload);
        $event = $this->prepareEvent($eventPayload);

        $this->send($event);
    }

    /**
     * Catches fatal errors being called on script exit
     */
    public function catchFatal(): void
    {
        $error = error_get_last();
        if ($error === null) {
            return;
        }

        $payload = [
            'exception' => new ErrorException(
                $error['message'],
                0,
                $error['type'],
                $error['file'],
                $error['line']
            )
        ];

        $eventPayload = $this->eventPayloadFactory->create($payload);
        $event = $this->prepareEvent($eventPayload);

        $this->send($event);
    }

    /**
     * Enable Catcher handlers functions for Exceptions, Errors and Shutdowns
     */
    public function enableHandlers(): void
    {
        /**
         * Catch uncaught exceptions
         */
        set_exception_handler([$this, 'catchException']);

        /**
         * Catch errors
         * By default if $errors equals True then catch all errors
         */
        set_error_handler([$this, 'catchError'], $this->options->getErrorTypes());

        /**
         * Catch fatal errors
         */
        register_shutdown_function([$this, 'catchFatal']);
    }

    /**
     * Prepares event and returns it
     *
     * @param EventPayload $eventPayload
     *
     * @return Event
     */
    private function prepareEvent(EventPayload $eventPayload): Event
    {
        $eventPayload->setRelease($this->options->getRelease());
        $event = new Event(
            $this->options->getAccessToken(),
            $eventPayload
        );

        return $event;
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
