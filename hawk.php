<?php
/**
 * Created by PhpStorm.
 * User: nostr
 * Date: 10.06.17
 * Time: 8:54
 */

class HawkErrorManager
{
    const API_URLS = array(
        "https://e9a0dc9c.ngrok.io"
    );

    public static function init() {
        set_error_handler(array('HawkErrorManager', 'Log'), E_ALL);
        set_exception_handler(array('HawkErrorManager', 'LogException'));
        error_reporting(E_ALL | E_STRICT);
    }

    public static function Log($errno, $errstr, $errfile, $errline, $errcontext) {

        $data = array(
            "error_type" => $errno,
            "error_description" => $errstr,
            "error_file" => $errfile,
            "error_line" => $errline,
            "error_context" => $errcontext,
            "debug_backtrace" => debug_backtrace()
        );

        HawkErrorManager::send($data);

    }

    public static function LogException($exception) {
        self::Log(E_ERROR, $exception->getMessage(), $exception->getFile(), $exception->getLine(), []);
    }

    /*******************/
    /* Private section */
    /*******************/

    private static function send($package) {
        foreach (HawkErrorManager::API_URLS as $api_url) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $api_url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($package));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $server_output = curl_exec ($ch);
            curl_close ($ch);
        }

    }
}

