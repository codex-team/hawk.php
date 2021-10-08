<?php

declare(strict_types=1);

namespace Hawk;

use ReflectionFunctionAbstract;
use Throwable;

/**
 * Class Stacktrace
 *
 * @package Hawk\Util
 */
final class StacktraceFrameBuilder
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * StacktraceFrameBuilder constructor.
     *
     * @param Serializer $serializer
     */
    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

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
    public function buildStack(Throwable $exception): array
    {
        /**
         * Get trace to exception
         */
        $stack = $exception->getTrace();

        /**
         * Prepare new stack to be filled
         */
        $newStack = [];

        /**
         * Frames iterator
         */
        $i = 0;

        /**
         * Add real error's path to trace chain
         *
         * Stack does not contain the latest (real) event frame
         * so we use getFile() and getLine() exception's methods
         * to get data for sources.
         */
        $newStack[$i] = [
            'file'       => $exception->getFile(),
            'line'       => $exception->getLine(),
            'sourceCode' => $this->getAdjacentLines($exception->getFile(), $exception->getLine()),
        ];

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
            if ($exception->getFile() == $callee['file'] && $exception->getLine() == $callee['line']) {
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

            $frame = $stack[$index];

            /**
             * Compose new frame
             */
            $newStack[++$i] = [
                'file'       => $frame['file'],
                'line'       => $frame['line'],
                'sourceCode' => $this->getAdjacentLines($frame['file'], $frame['line']),
            ];

            /**
             * Fill function and arguments data for the previous frame
             *
             * Each stack's frame contains data about the called method
             * and it's arguments but this data is useful only for
             * the previous frame because we get source code line
             * for that method.
             *
             * For the oldest frame (the last frame in the stack) we have
             * no method (and arguments) because it is an entry point
             * for the script. Then these fields for the last stack
             * frame $i will be empty.
             */
            $newStack[$i - 1]['function'] = $this->composeFunctionName($frame);
            $newStack[$i - 1]['arguments'] = $this->getArgs($frame);
        }

        return $newStack;
    }

    /**
     * Compose function name with a class for frame
     *
     * @param array $frame - backtrace frame
     *
     * @return string
     */
    private function composeFunctionName(array $frame): string
    {
        /**
         * Set an empty function name to be returned
         */
        $functionName = '';

        /**
         * Fill name with a class name and type '::' or '->'
         */
        if (!empty($frame['class'])) {
            $functionName = $frame['class'] . $frame['type'];
        }

        /**
         * Add a real function name
         */
        $functionName .= $frame['function'];

        return $functionName;
    }

    /**
     * Get function arguments for a frame
     *
     * @param array $frame - backtrace frame
     *
     * @return array
     */
    private function getArgs(array $frame): array
    {
        /**
         * Defining an array of arguments to be returned
         */
        $arguments = [];

        /**
         * If args param is not exist or empty
         * then return empty args array
         */
        if (empty($frame['args'])) {
            return $arguments;
        }

        /**
         * ReflectionFunction/ReflectionMethod class reports information
         * about a function/method.
         */
        $reflection = $this->getReflectionMethod($frame);

        /**
         * If reflection function in missing then create a simple list of arguments
         */
        if (!$reflection) {
            foreach ($frame['args'] as $index => $value) {
                $arguments['arg' . $index] = $value;
            }
        } else {
            /**
             * Get reflection params
             */
            $reflectionParams = $reflection->getParameters();

            /**
             * Passing through reflection params to get real names for values
             */
            foreach ($reflectionParams as $reflectionParam) {
                $paramName = $reflectionParam->getName();
                $paramPosition = $reflectionParam->getPosition();

                if (isset($frame['args'][$paramPosition])) {
                    $arguments[$paramName] = $frame['args'][$paramPosition];
                }
            }
        }

        /**
         * @todo Remove the following code when hawk.types
         *       supports non-iterable list of arguments
         */
        $newArguments = [];
        foreach ($arguments as $name => $value) {
            $value = $this->serializer->serializeValue($value);

            try {
                $newArguments[] = sprintf('%s = %s', $name, $value);
            } catch (\Exception $e) {
                // Ignore unknown types
            }
        }

        $arguments = $newArguments;

        return $arguments;
    }

    /**
     * Trying to create a reflection method
     *
     * @param array $frame - backtrace frame
     *
     * @return \ReflectionFunction|\ReflectionMethod|null
     */
    private function getReflectionMethod(array $frame): ?ReflectionFunctionAbstract
    {
        /**
         * Trying to create a correct reflection
         */
        try {
            /**
             * If we know class and method
             */
            if (!empty($frame['class']) && !empty($frame['function'])) {
                return new \ReflectionMethod($frame['class'], $frame['function']);
            }

            /**
             * If class name is missing then create a non-class function
             */
            if (empty($frame['class'])) {
                return new \ReflectionFunction($frame['function']);
            }
        } catch (\ReflectionException $e) {
            // Cannot create a reflection
        }

        /**
         * Return null if we cannot create a reflection
         */
        return null;
    }

    /**
     * Get path of file near target line to return as array
     *
     * @param string $filepath path to source file
     * @param int    $line     number of the target line
     * @param int    $margin   max number of lines before and after target line
     *                         to be returned
     *
     * @return array
     */
    private function getAdjacentLines(string $filepath, int $line, int $margin = 5): array
    {
        if (!file_exists($filepath) || !is_readable($filepath)) {
            return [];
        }

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
                $lineContent = $fileLines[$line];

                /**
                 * Remove line breaks
                 */
                $lineContent = preg_replace("/\r|\n/", '', $lineContent);

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
