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

        /**
         * List of $_SERVER headers vars without HTTP_ at the start
         */
        $otherHeadersVars = [
            'CONTENT_TYPE'   => 'Content-Type',
            'CONTENT_LENGTH' => 'Content-Length',
            'CONTENT_MD5'    => 'Content-Md5'
        ];

        foreach ($_SERVER as $name => $value) {
            /**
             * If $_SERVER key starts with 'HTTP_' then it is a header
             */
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
            /**
             * If this is header in $_SERVER without HTTP_ in the name
             */
            } elseif (in_array($name, $otherHeadersVars) && $value) {
                $headers[$otherHeadersVars[$name]] = $value;
            }
        }

        /**
         * Add Authorization header if not exist
         */
        if (!isset($headers['Authorization'])) {
            /**
             * Check for rewriten header by PHP-CGI
             */
            if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                $headers['Authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];

            /**
             * When doing HTTP authentication this variable is set
             * to the username provided by the user.
             */
            } elseif (isset($_SERVER['PHP_AUTH_USER'])) {
                /**
                 * When doing HTTP authentication this variable is set
                 * to the password provided by the user.
                 */
                $basic_pass = $_SERVER['PHP_AUTH_PW'] ?? '';
                $headers['Authorization'] = 'Basic ' . base64_encode($_SERVER['PHP_AUTH_USER'] . ':' . $basic_pass);

            /**
             * When doing Digest HTTP authentication this variable is set
             * to the 'Authorization' header sent by the client (which you
             * should then use to make the appropriate validation).
             */
            } elseif (isset($_SERVER['PHP_AUTH_DIGEST'])) {
                $headers['Authorization'] = $_SERVER['PHP_AUTH_DIGEST'];
            }
        }

        /**
         * Sort headers by key
         */
        ksort($headers);

        return $headers;
    }
}
