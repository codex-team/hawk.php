<?php

namespace Hawk;

/**
 * Hawk PHP catcher
 * Singleton Pattern
 *
 * @copyright Codex Team
 * @example https://hawk.so/docs#add-server-handler
 *
 *        Use namespaces
 *        > use \Hawk\HawkErrorManager;
 *
 *        Initialize Hawk this way
 *        > HawkErrorManager::instance('abcd1234-1234-abcd-1234-123456abcdef');
 *
 *        Or this way if you want to use custom Hawk server
 *        > HawkErrorManager::instance(
 *        >         'abcd1234-1234-abcd-1234-123456abcdef',
 *        >         'http://myownhawk.coms/catcher/php'
 *        > );
 *
 */
class HawkErrorManager
{
    private static $_instance;

    /**
     * Define error handlers
     */
    private function __construct ($accessToken) {

      self::$_accessToken = $accessToken;

      register_shutdown_function(array('\Hawk\HawkErrorManager', 'checkForFatal'));
      set_error_handler(array('\Hawk\HawkErrorManager', 'Log'), E_ALL);
      set_exception_handler(array('\Hawk\HawkErrorManager', 'LogException'));
      error_reporting(E_ALL | E_STRICT);

    }

    /**
     * @param $_url [String] - hawk server catcher URL
     */
    private static $_url = 'https://hawk.so/catcher/php';

    /**
     * @param $_accessToken [String] - project access token. Generated on https://hawk.so
     */
    private static $_accessToken;

    private function __clone () {}

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
     * Fatal errors catch method
     */
    static public function checkForFatal () {

      $error = error_get_last();

      if ( $error['type'] == E_ERROR )
          self::Log($error['type'], $error['message'], $error['file'], $error['line'], []);

    }

    /**
     * Construct logs package and send them to service with access token
     */
    public static function Log ($errno, $errstr, $errfile, $errline, $errcontext) {

        $data = array(
            "error_type" => $errno,
            "error_description" => $errstr,
            "error_file" => $errfile,
            "error_line" => $errline,
            "error_context" => $errcontext,
            "debug_backtrace" => debug_backtrace(),
            'http_params' => $_SERVER,
            "access_token" => self::$_accessToken
        );

        self::send($data);
    }

    /**
     * Construct Exceptions and send them to Logs
     */
    static public function LogException ($exception) {
        self::Log(E_ERROR, $exception->getMessage(), $exception->getFile(), $exception->getLine(), []);
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
    }

}
