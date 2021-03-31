<?php

declare(strict_types=1);

namespace Hawk;

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
     * SDK handler: contains methods that catchs errors and exceptions
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
     * @param array $payload
     *
     * @example
     * \Hawk\Catcher::get()
     *  ->catchEvent([
     *      'message' => 'my special message'
     *  ])
     */
    public function sendEvent(array $payload)
    {
        $this->handler->catchEvent($payload);
    }

    /**
     * @param Throwable $throwable
     * @param array     $context
     *
     * @example
     * \Hawk\Catcher::get()
     *  ->catchException($exception, [
     *      'message' => 'my special message'
     *  ])
     */
    public function sendException(Throwable $throwable, array $context = [])
    {
        $this->handler->catchException($throwable);
    }

    /**
     * @param array $options
     */
    private function __construct(array $options)
    {
        $options = new Options($options);
        $factory = new EventPayloadFactory();
        $transport = new CurlTransport($options->getUrl());

        $this->handler = new Handler($options, $transport, $factory);
        $this->handler->enableHandlers();
    }
}
