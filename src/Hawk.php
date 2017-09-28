<?php


namespace Hawk;

/**
 * Hawk PHP Catcher
 *
 * @copyright Codex Team
 * @example https://hawk.so/docs#add-server-handler
 */
class HawkCatcher
{
    /**
     * Define error handlers
     */
    private function __construct ($accessToken) {
        self::$_accessToken = $accessToken;
    }

    /**
     * Hawk instance
     */
    private static $_instance;

    /**
     * Set private functions cause Singleton
     */
    private function __clone () {}
    private function __sleep () {}
    private function __wakeup () {}

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
     */
    public static function instance ($accessToken, $url = '') {
        if ($url) {
            self::$_url = $url;
        }

        if (!self::$_instance) {
            self::$_instance = new self($accessToken);
        }

        return self::$_instance;
    }

    /**
     * Enable Hawk handlers functions for Exceptions, Error and Shutdown.
     *
     * @param $exceptions (bool)       (TRUE) enable catching exceptions
     * @param $errors (bool)           (TRUE) enable catching errors
     * @param $shutdown (bool)         (TRUE) enable catching shutdown
     */
    static public function enableHandlers($exceptions = TRUE, $errors = TRUE, $shutdown = TRUE) {

        if ($exceptions) {
            set_exception_handler(array('\Hawk\HawkCatcher', 'catchException'));
        }

        if ($errors) {
            set_error_handler(array('\Hawk\HawkCatcher', 'catchError'), E_ALL);
        }

        if ($shutdown) {
            register_shutdown_function(array('\Hawk\HawkCatcher', 'catchFatal'));
        }
    }

    /**
     * Construct Exceptions and send them to Logs
     */
    static public function catchException ($exception) {
        return self::prepare($exception->getCode(), $exception->getMessage(), $exception->getFile(), $exception->getLine(), []);
    }

    /**
     * Works automatically. PHP would call this function on error by himself.
     */
    static public function catchError ($errno, $errstr, $errfile, $errline, $errcontext) {
        return self::prepare($errno, $errstr, $errfile, $errline, $errcontext);
    }

    /**
     * Fatal errors catch method
     */
    static public function catchFatal () {
        $error = error_get_last();

        if ( $error['type'] ) {
            return self::prepare($error['type'], $error['message'], $error['file'], $error['line'], []);
        }
    }

    /**
     * Construct logs package and send them to service with access token
     */
    private static function prepare ($errno, $errstr, $errfile, $errline, $errcontext) {
        $data = array(
            "error_type" => $errno,
            "error_description" => $errstr,
            "error_file" => $errfile,
            "error_line" => $errline,
            "error_context" => $errcontext,
            "debug_backtrace" => debug_backtrace(),
            'http_params' => $_SERVER,
            "access_token" => self::$_accessToken,
            "GET" => $_GET,
            "POST" => $_POST
        );

        return self::send($data);
    }

    /**
     * Send package to service defined by api_url from settings
     */
    private static function send ($package) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::$_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($package));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        curl_close($ch);

        return $server_output;
    }
}
