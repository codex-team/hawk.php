<?php

declare(strict_types=1);

namespace Hawk\Helper;

use Throwable;

final class Stacktrace
{
    /**
     * Build exception backtrace.
     *
     * If you call debug_backtrace of getTrace functions then may return many
     * useless calls of processing the error by your framework. We are going
     * to go by stack from the entry point until we find string with error.
     * Then we can throw away all unnecessary calls.
     * Not always string with error will be in stack. So if we find it,
     * we will throw it away too. We have enough information to add this last
     * call manually.
     *
     * @param Throwable $exception
     *
     * @return array
     */
    public static function buildStack(Throwable $exception): array
    {
        /**
         * If exception was not passed then return full backtrace
         */
        if (!isset($exception)) {
            return debug_backtrace();
        }

        /**
         * Get trace to exception
         */
        $stack = $exception->getTrace();

        /**
         * Get real exception position
         */
        $errorPosition = [
            'file' => $exception->getFile(),
            'line' => $exception->getLine()
        ];

        /**
         * Reverse stack to go through from first call in project's entry point
         */
        $stack = array_reverse($stack);

        /**
         * This flag tells us that we have already found string with error
         * in stack and all following calls should be removed
         */
        $isErrorPositionWasFound = false;

        /**
         * Go through the stack
         */
        foreach ($stack as $index => $callee) {

            /**
             * Ignore callee if we don't khow it's filename
             */
            $isCalleeHasNoFile = empty($callee['file']);

            /**
             * Add ignore rules
             * - check if filepath is empty
             * - check if we have found real error in stack
             */
            if ($isCalleeHasNoFile || $isErrorPositionWasFound) {
                /**
                 * Remove this call
                 */
                unset($stack[$index]);
                continue;
            }

            /**
             * Is it our error? Check for a file and line similarity
             */
            if ($errorPosition['file'] == $callee['file'] && $errorPosition['line'] == $callee['line']) {
                /**
                 * We have found error in stack
                 * Then we can ignore all other calls
                 */
                $isErrorPositionWasFound = true;

                /**
                 * Remove this call
                 * We will add it here manually later
                 */
                unset($stack[$index]);
                continue;
            }

            /**
             * Save a couple of lines in the file by number of target line
             */
            $stack[$index]['trace'] = self::getAdjacentLines($callee['file'], $callee['line']);
        }

        /**
         * Add real error's path to trace chain
         */
        $stack[] = [
            'file'  => $errorPosition['file'],
            'line'  => $errorPosition['line'],
            'trace' => self::getAdjacentLines($errorPosition['file'], $errorPosition['line'])
        ];

        /**
         * Normalize array's indexes
         */
        $stack = array_values($stack);

        /**
         * Reverse stack back to have the latest call at the start of array
         */
        $stack = array_reverse($stack);

        return $stack;
    }

    /**
     * Get path of file near target line to return as array
     *
     * @param string $filepath
     * @param int    $line
     * @param int    $margin   max number of lines before and after target line
     *                         to be returned
     *
     * @return array
     */
    private static function getAdjacentLines(string $filepath, int $line, int $margin = 5): array
    {
        /**
         * Get file as array of lines
         */
        $fileLines = file($filepath);

        /**
         * In the file lines are counted from 1 but in array first element
         * is on 0 position. So to get line position in array
         * we need to decrease real line by 1
         */
        $errorLineInArray = $line - 1;

        /**
         * Get upper and lower lines positions to return part of file
         */
        $firstLine = $errorLineInArray - $margin;
        $lastLine = $errorLineInArray + $margin;

        /**
         * Create an empty array to be returned
         */
        $nearErrorFileLines = [];

        /**
         * Read file from $firstLine to $lastLine by lines
         */
        for ($line = $firstLine; $line <= $lastLine; $line++) {
            /**
             * Check if line doesn't exist. For elements positions in array before 0
             * and after end of file will be returned NULL
             */
            if (!empty($fileLines[$line])) {
                /**
                 * Escape HTML chars
                 */
                $lineContent = htmlspecialchars($fileLines[$line]);

                /**
                 * Add new line
                 */
                $nearErrorFileLines[] = [
                    /**
                     * Save real line
                     */
                    'line'    => $line + 1,
                    'content' => $lineContent
                ];
            }
        }

        return $nearErrorFileLines;
    }
}
