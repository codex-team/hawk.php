<?php

namespace Hawk\Monolog;

use Hawk\HawkCatcher;
use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;

/**
 * Class MonologHandler
 *
 * @example
 *
 * @package Hawk\Monolog
 */
class Handler extends AbstractProcessingHandler
{
    /**
     * Contructor sets up a Hawk catcher
     *
     * @param string $token     Project's token from hawk.so
     * @param int    $level     The minimum logging level at which this handler will be triggered
     * @param bool   $bubble    Whether the messages that are handled can bubble up the stack or not
     *
     * @throws \Hawk\MissingExtensionException
     */
    public function __construct($token, $level = Logger::DEBUG, $bubble = true)
    {
        parent::__construct($level, $bubble);

        HawkCatcher::instance($token, 'localhost:3000/catcher/php');
    }

    /**
     * Process log from monolog
     *
     * @param array $record
     */
    protected function write(array $record)
    {
        /**
         * Get log context
         */
        $context = isset($record['context']) ? $record['context'] : null;

        /**
         * Try to get 'exception' property from 'context'
         */
        $exception = isset($context['exception']) ? $context['exception'] : null;
        unset($context['exception']);

        /**
         * If $exception is null then try to get event data from context
         * Also remove exception data from $context not to send with event
         */
        if (!$exception) {
            /**
             * Get exception message
             */
            $message = isset($context['message']) ? $context['message'] : null;
            unset($context['message']);

            /**
             * Get exception code
             */
            $code = isset($context['code']) ? $context['code'] : null;
            unset($context['code']);

            /**
             * Get path to file with exception
             */
            $file = isset($context['file']) ? $context['file'] : null;
            unset($context['file']);

            /**
             * Get line in the file exception
             */
            $line = isset($context['line']) ? $context['line'] : null;
            unset($context['line']);

            /**
             * If at least one of these params is missing then ignore log
             */
            if (!$message || !$code || !$file || !$line) {
                return;
            }

            /**
             * Create an exception to be throwen
             */
            $exception = new \ErrorException(
                $message,
                $code,
                null,
                $file,
                $line
            );
        }

        HawkCatcher::catchException($exception, $context);
    }
}
