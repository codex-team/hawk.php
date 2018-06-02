<?php

namespace Hawk\Helper;

class Headers
{
    /**
     * Helps to get headers from $_SERVER variable because function
     * getallheaders() doesn't work on all systems.
     *
     * Find vars with HTTP_ in the start of keys and process these keys
     *
     * @example
     * $_SERVER['HTTP_USER_AGENT']                -> $headers['User-Agent']
     * $_SERVER['HTTP_ACCEPT_ENCODING']           -> $headers['Accept-Encoding']
     * $_SERVER['HTTP_UPGRADE_INSECURE_REQUESTS'] -> $headers['Upgrade-Insecure-Requests']
     *
     * @return array $headers
     */
    public static function get()
    {
        $headers = [];

        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                /**
                 * Remove HTTP_ from the start of key
                 *
                 * "HTTP_USER_AGENT" -> "USER_AGENT"
                 */
                $headerName = substr($name, 5);

                /**
                 * Replace all underscores to spaces
                 *
                 * "USER_AGENT" -> "USER AGENT"
                 */
                $headerName = str_replace('_', ' ', $headerName);

                /**
                 * Lowercase string
                 *
                 * "USER AGENT" -> "user agent"
                 */
                $headerName = strtolower($headerName);

                /**
                 * Uppercase words
                 *
                 * "user agent" -> "User Agent"
                 */
                $headerName = ucwords($headerName);

                /**
                 * Replace all spaces to hyphens
                 *
                 * "User Agent" -> "User-Agent"
                 */
                $headerName = str_replace(' ', '-', $headerName);

                /**
                 * Save header with right key to separate array
                 */
                $headers[$headerName] = $value;
            }
        }

        /**
         * Sort headers by key
         */
        ksort($headers);

        return $headers;
    }
}
