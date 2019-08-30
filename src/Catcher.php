<?php

declare(strict_types=1);

namespace Hawk;

use ErrorException;
use Hawk\Exception\MissingExtensionException;
use Throwable;

/**
 * Hawk PHP Catcher
 *
 * @copyright CodeX Team
 *
 * @see https://hawk.so/docs#add-server-handler
 */
class Catcher
{
    /**
     * Hawk instance
     */
    private static $instance;

    /**
     * Default Hawk server catcher URL
     */
    private static $url = 'https://hawk.so/catcher/php';

    /**
     * Project access token. Generated on https://hawk.so
     */
    private static $accessToken;

    /**
     * Main instance method
     *
     * @param string $accessToken
     * @param string $url
     *
     * @return Catcher
     *
     * @throws MissingExtensionException
     */
    public static function instance(string $accessToken, string $url = ''): self
    {
        /**
         * If php-curl is not available then throw an exception
         */
        if (!extension_loaded('curl')) {
            throw new MissingExtensionException('The cURL PHP extension is required to use the Hawk PHP Catcher');
        }

        /**
         * Update Catcher's URL
         */
        if ($url) {
            self::$url = $url;
        }

        /**
         * Singleton
         */
        if (!self::$instance) {
            self::$instance = new self($accessToken);
        }

        return self::$instance;
    }

    /**
     * Enable Hawk handlers functions for Exceptions, Errors and Shutdowns
     *
     * @example catch everything
     * \Hawk\HawkCatcher::enableHandlers();
     * @example catch only fatals
     * \Hawk\HawkCatcher::enableHandlers(
     *     false,      // exceptions
     *     false,      // errors
     *     true        // shutdown
     * );
     * @example catch only target types of error
     *          enter a bitmask of error types as second param
     *          by default TRUE converts to E_ALL
     *
     * @see http://php.net/manual/en/errorfunc.constants.php
     * \Hawk\HawkCatcher::enableHandlers(
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
    public static function enableHandlers(
        bool $exceptions = true,
        $errors = true,
        bool $shutdown = true
    ): void {
        /**
         * Catch uncaught exceptions
         */
        if ($exceptions) {
            set_exception_handler([Catcher::class, 'catchException']);
        }

        /**
         * Catch errors
         * By default if $errors equals True then catch all errors
         */
        $errors = $errors === true ? null : $errors;
        if ($errors) {
            set_error_handler([Catcher::class, 'catchError'], $errors);
        }

        /**
         * Catch fatal errors
         */
        if ($shutdown) {
            register_shutdown_function([Catcher::class, 'catchFatal']);
        }
    }

    /**
     * Process given exception
     *
     * @param Throwable $exception
     * @param array     $context   array of data to be passed with event
     *
     * @return bool
     */
    public static function catchException(Throwable $exception, array $context = []): bool
    {
        /**
         * If $context is not array then clean it up
         */
        if (!is_array($context)) {
            $context = [];
        }

        /**
         * Process exception
         */
        return self::processException($exception, $context);
    }

    /**
     * Errors catcher. PHP would call this function on error by himself
     *
     * @param int    $errCode
     * @param string $errMessage
     * @param string $errFile
     * @param int    $errLine
     * @param array  $context
     *
     * @return bool
     */
    public static function catchError(
        int $errCode,
        string $errMessage,
        string $errFile,
        int $errLine,
        array $context
    ): bool {
        /**
         * Create an exception with error's data
         */
        $exception = new ErrorException($errMessage, $errCode, null, $errFile, $errLine);

        /**
         * Process exception
         *
         * Ignore $context because there are global variables
         * as POST, ENV, SERVER etc. We will get them later.
         */
        return self::processException($exception);
    }

    /**
     * Fatal errors catch method
     * Being called on script exit
     *
     * @return bool|null
     */
    public static function catchFatal(): ?bool
    {
        /**
         * Get the last occurred error
         */
        $error = error_get_last();

        /**
         * Check if last error has a message
         * Otherwise the script has been executed successfully
         */
        if ($error['message']) {
            /**
             * Create an exception with error's data
             */
            $exception = new ErrorException(
                $error['message'],
                $error['type'],
                null,
                $error['file'],
                $error['line']
            );

            /**
             * Process exception
             */
            return self::processException($exception);
        }

        return null;
    }

    /**
     * Construct logs package and send them to service with access token
     *
     * @param Throwable $exception
     * @param array     $context   array of data to be passed with event
     *
     * @return bool
     */
    public static function processException(Throwable $exception, array $context = []): bool
    {
        /**
         * Get exception data
         *
         * If no code was passed then mark event as notice
         */
        $errCode = $exception->getCode() ?: E_NOTICE;
        $errMessage = $exception->getMessage();
        $errFile = $exception->getFile();
        $errLine = $exception->getLine();

        /**
         * Get stack
         */
        $stack = Helper\Stack::buildStack($exception);

        /**
         * Compose event's data
         */
        $data = [
            'token'        => self::$accessToken,
            'catcher_type' => 'errors/php',
            'payload'      => [
                /** Exception data */
                'error_type'        => $errCode,
                'error_description' => $errMessage,
                'error_file'        => $errFile,
                'error_line'        => $errLine,
                'error_context'     => $context,
                'debug_backtrace'   => $stack,

                /** Environment variables */
                'http_params' => $_SERVER,
                'GET'         => $_GET,
                'POST'        => $_POST,
                'COOKIES'     => $_COOKIE,
                'HEADERS'     => Helper\Headers::get()
            ]
        ];

        /**
         * Send event to Hawk
         */
        return self::send($data);
    }

    /**
     * Send package to service defined by api_url from settings
     *
     * @param array $package
     *
     * @return bool - return true on success and false otherwise
     */
    private static function send(array $package): bool
    {
        highlight_string("<?php\n" . var_export($package, true) . ";\n?>");

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::$url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($package));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $serverOutput = curl_exec($ch);
        curl_close($ch);

        return (bool) $serverOutput;
    }

    /**
     * Set Project's access token
     *
     * @param string $accessToken
     */
    private function __construct(string $accessToken)
    {
        self::$accessToken = $accessToken;
    }

    /**
     * Set private functions cause Singleton
     */
    private function __clone()
    {
    }

    private function __sleep()
    {
    }

    private function __wakeup()
    {
    }
}
