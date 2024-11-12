<?php

declare(strict_types=1);

namespace Hawk;

use Hawk\Addons\Headers;
use Hawk\Addons\Environment;
use Hawk\Transport\CurlTransport;
use Throwable;

/**
 * Hawk PHP Catcher SDK
 *
 * @copyright CodeX
 *
 * @see https://hawk.so/docs#add-server-handler
 */
final class Catcher
{
    /**
     * Catcher SDK private instance. Created once
     *
     * @var Catcher
     */
    private static $instance;

    /**
     * SDK handler: contains methods that catch errors and exceptions
     *
     * @var Handler
     */
    private $handler;

    /**
     * Static method to initialize Catcher
     *
     * @param array $options
     *
     * @return Catcher
     */
    public static function init(array $options): Catcher
    {
        if (!self::$instance) {
            self::$instance = new self($options);
        }

        return self::$instance;
    }

    /**
     * Returns initialized instance or throws an exception if it is not created yet
     *
     * @return Catcher
     *
     * @throws \Exception
     */
    public static function get(): Catcher
    {
        if (self::$instance === null) {
            throw new \Exception('Catcher is not initialized');
        }

        return self::$instance;
    }

    /**
     * @param array $user
     *
     * @return $this
     */
    public function setUser(array $user): self
    {
        $this->handler->setUser($user);

        return $this;
    }

    /**
     * @param array $context
     *
     * @return $this
     */
    public function setContext(array $context): self
    {
        $this->handler->setContext($context);

        return $this;
    }

    /**
     * @param string $message
     * @param array  $context
     *
     * @example
     * \Hawk\Catcher::get()
     *  ->sendMessage('my special message', [
     *      ... // context
     *  ])
     */
    public function sendMessage(string $message, array $context = []): void
    {
        $this->handler->sendEvent([
            'title'   => $message,
            'context' => $context
        ]);
    }

    /**
     * @param Throwable $throwable
     * @param array     $context
     *
     * @throws Throwable
     *
     * @example
     * \Hawk\Catcher::get()
     *  ->sendException($exception, [
     *      ... // context
     *  ])
     */
    public function sendException(Throwable $throwable, array $context = [])
    {
        $this->handler->handleException($throwable, $context);
    }

    /**
     * @example
     * \Hawk\Catcher::get()
     *  ->sendEvent([
     *      ... // payload
     * ])
     *
     * @param array $payload
     */
    public function sendEvent(array $payload): void
    {
        $this->handler->sendEvent($payload);
    }

    /**
     * @param array $options
     */
    private function __construct(array $options)
    {
        $options = new Options($options);

        /**
         * Init stacktrace frames builder and inject serializer
         */
        $serializer = new Serializer();
        $stacktraceBuilder = new StacktraceFrameBuilder($serializer);

        /**
         * Prepare Event payload builder
         */
        $builder = new EventPayloadBuilder($stacktraceBuilder);
        $builder->registerAddon(new Headers());
        $builder->registerAddon(new Environment());

        $transport = new CurlTransport($options->getUrl());

        $this->handler = new Handler($options, $transport, $builder);

        $this->handler->registerErrorHandler();
        $this->handler->registerExceptionHandler();
        $this->handler->registerFatalHandler();
    }
}
