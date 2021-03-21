<?php

declare(strict_types=1);

namespace Hawk;

use Hawk\Helper\Stacktrace;
use Throwable;

/**
 * Hawk PHP Catcher
 *
 * @copyright CodeX Team
 *
 * @see https://hawk.so/docs#add-server-handler
 */
final class Catcher
{
    /**
     * Hawk instance
     */
    private static $instance;

    /**
     * Default Hawk server catcher URL
     */
    private $url = 'https://hawk.so/catcher/php';

    /**
     * Project access token. Generated on https://hawk.so
     */
    private $accessToken;

    /**
     * Main instance method
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
     * @return Catcher
     *
     * @throws \Exception
     */
    public static function get(): Catcher
    {
        if (!self::$instance) {
            throw new \Exception('Init before');
        }

        return self::$instance;
    }

    /**
     * Enable Hawk handlers functions for Exceptions, Errors and Shutdowns
     *
     * @example catch everything
     * \Hawk\Catcher::enableHandlers();
     * @example catch only fatals
     * \Hawk\Catcher::enableHandlers(
     *     false,      // exceptions
     *     false,      // errors
     *     true        // shutdown
     * );
     * @example catch only target types of error
     *          enter a bitmask of error types as second param
     *          by default TRUE converts to E_ALL
     *
     * @see http://php.net/manual/en/errorfunc.constants.php
     * \Hawk\Catcher::enableHandlers(
     *     false,               // exceptions
     *     E_WARNING | E_PARSE, // Run-time warnings or compile-time parse errors
     *     true                 // shutdown
     * );
     *
     * @param bool     $exceptions (true) enable catching exceptions
     * @param bool|int $errors     (true) enable catching errors
     *                             You can pass a bitmask of error types
     *                             See an example above
     * @param bool     $shutdown   (true) enable catching shutdowns
     *
     * @return void
     */
    public function enableHandlers(bool $exceptions = true, bool $errors = true, bool $shutdown = true): void
    {
        /**
         * Catch uncaught exceptions
         */
        if ($exceptions) {
            set_exception_handler([$this, 'catchException']);
        }

        /**
         * Catch errors
         * By default if $errors equals True then catch all errors
         */
        $errors = $errors === true ? null : $errors;
        if ($errors) {
            set_error_handler([$this, 'catchError'], $errors);
        }

        /**
         * Catch fatal errors
         */
        if ($shutdown) {
            register_shutdown_function([$this, 'catchFatal']);
        }
    }

    /**
     * @param array $payload
     */
    public function catchEvent(array $payload)
    {
        $event = new Event();
        $event->setEventPayload(new EventPayload($payload));

        $this->send($event);
    }

    /**
     * Process given exception
     *
     * @param Throwable $exception
     * @param array     $context   array of data to be passed with event
     */
    public function catchException(Throwable $exception, array $context = []): void
    {
        $payload = [
            'title'     => $exception->getMessage(),
            'type'      => '',
            'timestamp' => time(),
            'level'     => 1
        ];

        // Prepare GET params
        if (!empty($_GET)) {
            $payload['getParams'] = $_GET;
        }

        // Prepare POST params
        if (!empty($_POST)) {
            $payload['postParams'] = $_POST;
        }

        if (!empty($context)) {
            $payload['context'] = $context;
        }

        $payload['backtrace'] = Stacktrace::buildStack($exception);

        $event = new Event();
        $event->setEventPayload(new EventPayload($payload));

        $this->send($event);
    }

    /**
     * Errors catcher. PHP would call this function on error by himself
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

        ];

        $event = new Event();
        $event->setEventPayload(new EventPayload($payload));

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
            'title'     => $error['message'],
            'type'      => $error['type'],
            'timestamp' => time(),
        ];

        $event = new Event();
        $event->setEventPayload(new EventPayload($payload));

        $this->send($event);
    }

    /**
     * Set Project's access token
     *
     * @param string $accessToken
     * @param string $url
     */
    private function __construct(string $accessToken, string $url = '')
    {
        $this->accessToken = $accessToken;

        if (!empty($url)) {
            $this->url = $url;
        }
    }

    /**
     * @param Event $event
     */
    private function send(Event $event): void
    {
        dd(json_encode($event));
    }

    /**
     * Send package to service defined by api_url from settings
     *
     * @param array $package
     *
     * @return bool - return true on success and false otherwise
     */
    private function _send(array $package): bool
    {
        return false;
    }
}
