<?php

declare(strict_types=1);

namespace Hawk;

/**
 * Class Serializer is used to serialize values before sending to the Hawk
 *
 * @package Hawk
 */
final class Serializer
{
    /**
     * Process any value and makes it safe (in appropriate format) to send to hawk
     *
     * @param $value
     *
     * @return string
     */
    public function serializeValue($value): string
    {
        $flags = JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT;
        $encoded = json_encode($this->prepare($value, 0), $flags);

        if ($encoded === false) {
            return '';
        }

        return $encoded;
    }

    /**
     * Max nesting depth to avoid runaway recursion on $GLOBALS and similar circular structures.
     */
    private const PREPARE_MAX_DEPTH = 32;

    /**
     * Prepares value for encoding
     *
     * @param mixed $value
     * @param int   $depth
     *
     * @return array|mixed|string
     */
    private function prepare($value, int $depth = 0)
    {
        if ($depth > self::PREPARE_MAX_DEPTH) {
            return '[max depth]';
        }

        if (!is_object($value) && (is_array($value) || is_iterable($value))) {
            $result = [];
            foreach ($value as $key => $subValue) {
                if (is_array($subValue) || is_iterable($subValue)) {
                    $result[$key] = $this->prepare($subValue, $depth + 1);
                } else {
                    $result[$key] = $this->transform($subValue);
                }
            }

            return $result;
        }

        return $this->transform($value);
    }

    /**
     * Transforms value to string or returns itself
     *
     * @param $value
     *
     * @return mixed|string
     */
    private function transform($value)
    {
        if (is_null($value)) {
            return 'null';
        } elseif (is_callable($value)) {
            return 'Closure';
        } elseif (is_object($value)) {
            return get_class($value);
        } elseif (is_resource($value)) {
            return 'Resource';
        } else {
            return $value;
        }
    }

    /**
     * Check array if it is associative
     *
     * @param array $array
     *
     * @return bool
     */
    private function isAssoc(array $array): bool
    {
        if ([] === $array) {
            return false;
        }

        return array_keys($array) !== range(0, count($array) - 1);
    }
}
