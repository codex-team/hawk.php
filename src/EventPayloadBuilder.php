<?php

declare(strict_types=1);

namespace Hawk;

use Hawk\Addons\AddonInterface;

/**
 * Class EventPayloadBuilder is a factory object
 *
 * @package Hawk
 */
class EventPayloadBuilder
{
    /**
     * List of addon resolvers
     *
     * @var array
     */
    private $addonsResolvers = [];

    /**
     * StacktraceFrameBuilder object. Used to parse exception stacktrace
     *
     * @var StacktraceFrameBuilder
     */
    private $stacktraceFrameBuilder;

    /**
     * Allowed keys for stacktrace frames
     */
    private const ALLOWED_KEYS = [
        'file',
        'line',
        'column',
        'sourceCode',
        'function',
        'arguments',
        'additionalData',
    ];

    /**
     * EventPayloadFactory constructor.
     */
    public function __construct(StacktraceFrameBuilder $stacktraceFrameBuilder)
    {
        $this->stacktraceFrameBuilder = $stacktraceFrameBuilder;
    }

    /**
     * Adds addon resolver to the list
     *
     * @param AddonInterface $addon
     *
     * @return $this
     */
    public function registerAddon(AddonInterface $addon): self
    {
        $this->addonsResolvers[] = $addon;

        return $this;
    }

    /**
     * Returns EventPayload object
     *
     * @param array $data - event payload
     *
     * @return EventPayload
     */
    public function create(array $data): EventPayload
    {
        $eventPayload = new EventPayload();

        if (!empty($data['title'])) {
            $eventPayload->setTitle($data['title']);
        }

        $eventPayload->setContext($data['context']);
        $eventPayload->setUser($data['user']);

        if (isset($data['exception']) && $data['exception'] instanceof \Throwable) {
            $exception = $data['exception'];
            $stacktrace = $this->stacktraceFrameBuilder->buildStack($exception);

            $eventPayload->setTitle($exception->getMessage() ?: get_class($exception));
        } else {
            $stacktrace = debug_backtrace();
        }

        /**
         * Normalize frames to BacktraceFrame shape and wrap extra fields in additionalData.
         * Also sanitize keys for MongoDB compatibility.
         */
        $stacktrace = $this->normalizeBacktrace($stacktrace);

        if (isset($data['type'])) {
            $eventPayload->setType($data['type']);
        }

        $eventPayload->setBacktrace($stacktrace);

        // Resolve addons
        $eventPayload->setAddons($this->resolveAddons());

        return $eventPayload;
    }

    /**
     * Resolves addons list and returns array
     *
     * @return array
     */
    private function resolveAddons(): array
    {
        $result = [];

        /**
         * @var string         $key
         * @var AddonInterface $resolver
         */
        foreach ($this->addonsResolvers as $key => $resolver) {
            $result[$resolver->getName()] = $resolver->resolve();
        }

        return $result;
    }

    /**
     * Normalize any stacktrace representation to BacktraceFrame shape
     * and wrap unknown fields into additionalData with safe keys
     *
     * @param array $stack
     *
     * @return array
     */
    private function normalizeBacktrace(array $stack): array
    {
        $normalized = [];

        foreach ($stack as $frame) {
            if (!is_array($frame)) {
                continue;
            }

            $file = isset($frame['file']) ? (string) $frame['file'] : '';
            $line = isset($frame['line']) ? (int) $frame['line'] : 0;
            $functionName = null;

            if (isset($frame['function'])) {
                if (!empty($frame['class']) && !empty($frame['type'])) {
                    $functionName = (string) $frame['class'] . (string) $frame['type'] . (string) $frame['function'];
                } else {
                    $functionName = (string) $frame['function'];
                }
            } elseif (isset($frame['functionName'])) {
                $functionName = (string) $frame['functionName'];
            }

            $additional = [];
            foreach ($frame as $key => $value) {
                if (!in_array($key, self::ALLOWED_KEYS, true)) {
                    // Drop heavy/unserializable objects from 'object' field; store class name instead
                    if ($key === 'object') {
                        $value = is_object($value) ? get_class($value) : $value;
                    }

                    $additional[$key] = $this->transformForJson($value);
                }
            }

            $normalized[] = $this->sanitizeArrayKeys([
                'file'          => $file,
                'line'          => $line,
                'column'        => null,
                'sourceCode'    => isset($frame['sourceCode']) && is_array($frame['sourceCode']) ? $frame['sourceCode'] : null,
                'function'      => $functionName,
                // Keep arguments only if it already looks like desired string[]; otherwise omit
                // Limit argument processing to first 10 items to avoid performance issues
                'arguments'     => (isset($frame['arguments']) && is_array($frame['arguments'])) 
                    ? array_values(array_map('strval', array_slice($frame['arguments'], 0, 10)))
                    : [],
                'additionalData'=> $additional,
            ]);
        }

        return $normalized;
    }

    /**
     * Recursively sanitize array keys to be MongoDB-safe
     * - replace dots with underscores
     * - replace leading '$' with 'dollar_'
     *
     * @param mixed $value
     *
     * @return mixed
     */
    private function sanitizeArrayKeys($value)
    {
        if (!is_array($value)) {
            return $value;
        }

        $sanitized = [];

        foreach ($value as $key => $subValue) {
            $newKey = $key;

            if (is_string($newKey)) {
                if (strpos($newKey, '.') !== false) {
                    $newKey = str_replace('.', '_', $newKey);
                }

                if (isset($newKey[0]) && $newKey[0] === '$') {
                    $newKey = 'dollar_' . substr($newKey, 1);
                }
            }

            $sanitized[$newKey] = $this->sanitizeArrayKeys($subValue);
        }

        return $sanitized;
    }

    /**
     * Transform values to JSON-serializable representation
     *
     * @param mixed $value
     *
     * @return mixed
     */
    private function transformForJson($value)
    {
        if (is_array($value)) {
            $result = [];
            foreach ($value as $k => $v) {
                $result[$k] = $this->transformForJson($v);
            }

            return $result;
        }

        if (is_null($value)) {
            return null;
        }

        if (is_callable($value)) {
            return 'Closure';
        }

        if (is_object($value)) {
            return get_class($value);
        }

        if (is_resource($value)) {
            return 'Resource';
        }

        return $value;
    }
}
