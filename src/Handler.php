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
class Handler
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
     * Events payload builder object
     *
     * @var EventPayloadBuilder
     */
    private $eventPayloadBuilder;

    /**
     * @var array
     */
    private $user = [];

    /**
     * @var array
     */
    private $context = [];

    /**
     * Handler constructor.
     *
     * @param Options             $options
     * @param TransportInterface  $transport
     * @param EventPayloadBuilder $eventPayloadBuilder
     */
    public function __construct(
        Options $options,
        TransportInterface $transport,
        EventPayloadBuilder $eventPayloadBuilder
    ) {
        $this->options = $options;
        $this->transport = $transport;
        $this->eventPayloadBuilder = $eventPayloadBuilder;
    }

    /**
     * @param array $user
     *
     * @return $this
     */
    public function withUser(array $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @param array $context
     *
     * @return $this
     */
    public function withContext(array $context): self
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Method to send manually any event to Hawk
     *
     * @param array $payload
     */
    public function catchEvent(array $payload): void
    {
        $payload['context'] = array_merge($this->context, $payload['context'] ?? []);
        $payload['user'] = $this->user;

        $eventPayload = $this->eventPayloadBuilder->create($payload);
        $event = $this->prepareEvent($eventPayload);

        if ($event !== null) {
            $this->send($event);
        }
    }

    /**
     * Process exception and send to Hawk
     *
     * @param Throwable $exception
     * @param array     $context   array of data to be passed with event
     */
    public function catchException(Throwable $exception, array $context = []): void
    {
        $data = [
            'exception' => $exception,
            'context'   => array_merge($this->context, $context),
            'user'      => $this->user
        ];

        $eventPayload = $this->eventPayloadBuilder->create($data);
        $event = $this->prepareEvent($eventPayload);

        if ($event !== null) {
            $this->send($event);
        }

        throw $exception;
    }

    /**
     * Catches error and sends to Hawk
     *
     * @param int    $level
     * @param string $message
     * @param string $file
     * @param int    $line
     *
     * @return bool
     */
    public function catchError(int $level, string $message, string $file, int $line): bool
    {
        $exception = new ErrorException($message, $level, 0, $file, $line);
        $data = [
            'exception' => $exception,
            'context'   => $this->context,
            'user'      => $this->user,
            'type'      => $exception->getSeverity()
        ];

        $eventPayload = $this->eventPayloadBuilder->create($data);
        $event = $this->prepareEvent($eventPayload);

        if ($event !== null) {
            $this->send($event);
        }

        return false;
    }

    /**
     * Catches fatal errors being called on script exit
     */
    public function catchFatal(): void
    {
        $error = error_get_last();
        if (
            $error === null
            || is_array($error) && $error['type'] && (\E_ERROR | \E_PARSE | \E_CORE_ERROR | \E_CORE_WARNING | \E_COMPILE_ERROR | \E_COMPILE_WARNING)
        ) {
            return;
        }

        $payload = [
            'exception' => new ErrorException(
                $error['message'],
                0,
                $error['type'],
                $error['file'],
                $error['line']
            ),
            'context'   => $this->context,
            'user'      => $this->user
        ];

        $eventPayload = $this->eventPayloadBuilder->create($payload);
        $event = $this->prepareEvent($eventPayload);

        if ($event !== null) {
            $this->send($event);
        }
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
     * @return null|Event
     */
    private function prepareEvent(EventPayload $eventPayload): ?Event
    {
        $eventPayload->setRelease($this->options->getRelease());
        $beforeSendCallback = $this->options->getBeforeSend();
        if ($beforeSendCallback) {
            $eventPayload = $beforeSendCallback($eventPayload);
            if ($eventPayload === null) {
                return null;
            }
        }
        $event = new Event(
            $this->options->getIntegrationToken(),
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
