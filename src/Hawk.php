<?php

namespace Hawk;

use Hawk\Helper;

/**
 * Hawk PHP Catcher
 *
 * @copyright CodeX Team
 * @see https://hawk.so/docs#add-server-handler
 */
class HawkCatcher
{
    /**
     * Hawk instance
     */
    private static $_instance;

    /**
     * Default Hawk server catcher URL
     */
    private static $_url = 'https://hawk.so/catcher/php';

    /**
     * Project access token. Generated on https://hawk.so
     */
    private static $_accessToken;

    /**
     * Main instance method
     *
     * @param string $accessToken
     * @param string $url
     *
     * @return HawkCatcher
     *
     * @throws MissingExtensionException
     */
    public static function instance($accessToken, $url = '')
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
            self::$_url = $url;
        }

        /**
         * Singleton helper
         */
        if (!self::$_instance) {
            self::$_instance = new self($accessToken);
        }

        return self::$_instance;
    }

    /**
     * Enable Hawk handlers functions for Exceptions, Errors and Shutdowns
     *
     * @example catch everything
     * \Hawk\HawkCatcher::enableHandlers();
     *
     * @example catch only fatals
     * \Hawk\HawkCatcher::enableHandlers(
     *     FALSE,      // exceptions
     *     FALSE,      // errors
     *     TRUE        // shutdown
     * );
     *
     * @example catch only target types of error
     *          enter a bitmask of error types as second param
     *          by default TRUE converts to E_ALL
     * @see http://php.net/manual/en/errorfunc.constants.php
     * \Hawk\HawkCatcher::enableHandlers(
     *     FALSE,               // exceptions
     *     E_WARNING | E_PARSE, // Run-time warnings or compile-time parse errors
     *     TRUE                 // shutdown
     * );
     *
     * @param bool         $exceptions       (TRUE) enable catching exceptions
     * @param bool|integer $catchTheseErrors (TRUE) enable catching errors
     *                                       You can pass a bitmask of error types
     *                                       See an example above
     * @param bool         $shutdown         (TRUE) enable catching shutdowns
     *
     * @return void
     */
    public static function enableHandlers(
        $exceptions = TRUE,
        $catchTheseErrors = TRUE,
        $shutdown = TRUE
    ) {
        /**
         * Catch uncaught exceptions
         */
        if ($exceptions) {
            set_exception_handler(array('\Hawk\HawkCatcher', 'catchException'));
        }

        /**
         * Catch errors
         * By default if $catchTheseErrors equals True then catch all errors
         */
        $catchTheseErrors = $catchTheseErrors === TRUE ? null : $catchTheseErrors;
        if ($catchTheseErrors) {
            set_error_handler(array('\Hawk\HawkCatcher', 'catchError'), $catchTheseErrors);
        }

        /**
         * Catch fatal errors
         */
        if ($shutdown) {
            register_shutdown_function(array('\Hawk\HawkCatcher', 'catchFatal'));
        }
    }

    /**
     * Process given exception
     *
     * @param \Exception $exception
     * @param array $context array of data to be passed with event
     *
     * @return string
     */
    public static function catchException($exception, $context = array())
    {
        /**
         * If $context is not array then clean it up
         */
        if (!is_array($context)) {
            $context = array();
        }

        /**
         * Process exception
         */
        return self::processException($exception, $context);
    }

    /**
     * @todo
     * Works automatically. PHP would call this function on error by himself.
     *
     * @param $errno
     * @param $errstr
     * @param $errfile
     * @param $errline
     * @param $errcontext
     *
     * @return string|boolean
     */
    public static function catchError($errno, $errstr, $errfile, $errline, $errcontext)
    {
        /**
         * Create an exception with error's data
         */
        $exception = new \ErrorException($errstr, $errno, null,  $errfile, $errline);

        /**
         * Process exception
         */
        return self::processException($exception);
    }

    /**
     * Fatal errors catch method
     * Being called on script exit
     *
     * @return string|boolean|void
     */
    public static function catchFatal()
    {
        /**
         * Get the last occured error
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
            $exception = new \ErrorException(
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
    }

    /**
     * Construct logs package and send them to service with access token
     *
     * @param \Exception $exception
     * @param array $context array of data to be passed with event
     *
     * @return string
     */
    public static function processException($exception, $context = array())
    {
        if (empty($exception)) {
            return "No exception was passed";
        }

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
        $data = array(
            /** Exception data */
            "error_type" => $errCode,
            "error_description" => $errMessage,
            "error_file" => $errFile,
            "error_line" => $errLine,
            "error_context" => $context,
            "debug_backtrace" => $stack,

            /** Project's token */
            "access_token" => self::$_accessToken,

            /** Environment variables */
            "http_params" => $_SERVER,
            "GET" => $_GET,
            "POST" => $_POST,
            "COOKIES" => $_COOKIE,
            "HEADERS" => Helper\Headers::get()
        );

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
     * @return string|bool - return string or bool
     *                        'OK' on success server response
     *                        'No access token' if no token was passed
     *                        false if curl request was failed
     */
    private static function send($package)
    {
        if ( !self::$_accessToken ) {
            return "No access token";
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::$_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($package));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $server_output = curl_exec($ch);
        curl_close($ch);

        return $server_output;
    }

    /**
     * Set Project's access token
     *
     * @param string $accessToken
     */
    private function __construct($accessToken)
    {
        self::$_accessToken = $accessToken;
    }

    /**
     * Set private functions cause Singleton
     */
    private function __clone() {}
    private function __sleep() {}
    private function __wakeup() {}
}
