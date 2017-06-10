<?php


class HawkErrorManager
{
    # Define error handlers and load configuration
    function __construct() {
        set_error_handler(array('HawkErrorManager', 'Log'), E_ALL);
        set_exception_handler(array('HawkErrorManager', 'LogException'));
        error_reporting(E_ALL | E_STRICT);

        $this->config = require_once("config.php");
    }

    # Construct logs package and send them to service with access token
    public function Log($errno, $errstr, $errfile, $errline, $errcontext) {

        $data = array(
            "error_type" => $errno,
            "error_description" => $errstr,
            "error_file" => $errfile,
            "error_line" => $errline,
            "error_context" => $errcontext,
            "debug_backtrace" => debug_backtrace(),

            # Access token obtained from official website
            "access_token" => $this->config['access_token']
        );

        $this->send($data);

    }

    # Construct Exceptions and send them to Logs
    public static function LogException($exception) {
        self::Log(E_ERROR, $exception->getMessage(), $exception->getFile(), $exception->getLine(), []);
    }

    /*******************/
    /* Private section */
    /*******************/

    # Send package to service defined by api_url from settings
    private function send($package) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->config['api_url']);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($package));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec ($ch);
        curl_close ($ch);
    }
}

