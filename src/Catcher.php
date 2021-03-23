<?php

declare(strict_types=1);

namespace Hawk;

use Hawk\Transport\CurlTransport;
use Throwable;

/**
 * Hawk PHP Catcher SDK
 *
 * @copyright CodeX Team
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
     * Default Hawk server catcher URL
     *
     * @var string
     */
    private $url = 'https://hawk.so/catcher/php';

    /**
     * SDK handler: contains methods that catchs errors and exceptions
     *
     * @var Handler
     */
    private $handler;

    /**
     * Static method to initialize Catcher
     *
     * @param string $accessToken
     * @param string $url
     *
     * @return Catcher
     */
    public static function init(string $accessToken, string $url = ''): Catcher
    {
        if (!self::$instance) {
            self::$instance = new self($accessToken, $url);
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
     * Enable Catcher handlers functions for Exceptions, Errors and Shutdowns
     *
     * @example catch everything
     * \Hawk\Catcher::init()->enableHandlers();
     * @example catch only fatals
     * \Hawk\Catcher::init()->enableHandlers(
     *     false,      // exceptions
     *     false,      // errors
     *     true        // shutdown
     * );
     * @example catch only target types of error
     *          enter a bitmask of error types as second param
     *          by default TRUE converts to E_ALL
     *
     * @see http://php.net/manual/en/errorfunc.constants.php
     * \Hawk\Catcher::init()->enableHandlers(
     *     false,               // exceptions
     *     E_WARNING | E_PARSE, // Run-time warnings or compile-time parse errors
     *     true                 // shutdown
     * );
     *
     * @param bool     $exceptions (true) enable catching exceptions
     * @param bool|int $errors     (true) enable catching errors
     *                             You can pass a bitmask of error types
     *                             See an example above
     * @param bool     $shutdown   (false) enable catching shutdowns
     *
     * @return void
     */
    public function enableHandlers(bool $exceptions = true, $errors = true, bool $shutdown = false): void
    {
        /**
         * Catch uncaught exceptions
         */
        if ($exceptions) {
            set_exception_handler([$this->handler, 'catchException']);
        }

        /**
         * Catch errors
         * By default if $errors equals True then catch all errors
         */
        $errors = $errors === true ? null : $errors;
        if ($errors) {
            set_error_handler([$this->handler, 'catchError'], $errors);
        }

        /**
         * Catch fatal errors
         */
        if ($shutdown) {
            register_shutdown_function([$this->handler, 'catchFatal']);
        }
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
    public function catchEvent(array $payload)
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
    public function catchException(Throwable $throwable, array $context = [])
    {
        $this->handler->catchException($throwable);
    }

    /**
     * @param string $accessToken
     * @param string $url
     */
    private function __construct(string $accessToken, string $url = '')
    {
        if (empty($url)) {
            $url = $this->url;
        }

        $this->handler = new Handler(
            new CurlTransport($url),
            $accessToken
        );
    }
}
