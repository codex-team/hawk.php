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
     * Long scalar strings: insert U+200B every N chars so UIs can wrap (like soft word-break),
     * without breaking JSON validity. Does not alter tokens like short keys.
     */
    private const SOFT_BREAK_EVERY_CHARS = 72;

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
        } elseif (is_string($value)) {
            return $this->insertSoftBreaksInString($value);
        } else {
            return $value;
        }
    }

    /**
     * Insert zero-width spaces for long strings so Hawk (or any monospace view) can wrap
     * without CSS word-break; JSON remains valid after json_encode.
     */
    private function insertSoftBreaksInString(string $value): string
    {
        $len = strlen($value);
        if ($len <= self::SOFT_BREAK_EVERY_CHARS) {
            return $value;
        }

        $chunk = self::SOFT_BREAK_EVERY_CHARS;
        $zwsp = "\u{200B}";

        if (function_exists('mb_str_split')) {
            $parts = mb_str_split($value, $chunk, 'UTF-8');

            return implode($zwsp, $parts);
        }

        return implode($zwsp, str_split($value, $chunk));
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
