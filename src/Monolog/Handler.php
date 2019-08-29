<?php

declare(strict_types=1);

namespace Hawk\Monolog;

use ErrorException;
use Hawk\Catcher;
use Hawk\Exception\MissingExtensionException;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

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
     * Constructor sets up a Hawk catcher
     *
     * @param string $token  Project's token from hawk.so
     * @param int    $level  The minimum logging level at which this handler will be triggered
     * @param bool   $bubble Whether the messages that are handled can bubble up the stack or not
     * @param string $url    path to catcher on custom server
     *
     * @throws MissingExtensionException
     */
    public function __construct(string $token, int $level = Logger::DEBUG, bool $bubble = true, string $url = '')
    {
        parent::__construct($level, $bubble);

        Catcher::instance($token, $url);
    }

    /**
     * Process log from monolog
     *
     * @param array $record
     */
    protected function write(array $record): void
    {
        /**
         * Get log context
         */
        $context = $record['context'] ?? null;

        /**
         * Try to get 'exception' property from 'context'
         */
        $exception = $context['exception'] ?? null;
        unset($context['exception']);

        /**
         * If $exception is null then try to get event data from context
         * Also remove exception data from $context not to send with event
         */
        if (!$exception) {
            /**
             * Get exception message
             */
            $message = $context['message'] ?? null;
            unset($context['message']);

            /**
             * Get exception code
             */
            $code = $context['code'] ?? null;
            unset($context['code']);

            /**
             * Get path to file with exception
             */
            $file = $context['file'] ?? null;
            unset($context['file']);

            /**
             * Get line in the file exception
             */
            $line = $context['line'] ?? null;
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
            $exception = new ErrorException(
                $message,
                $code,
                null,
                $file,
                $line
            );
        }

        Catcher::catchException($exception, $context);
    }
}
