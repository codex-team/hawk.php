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
        $encoded = json_encode($this->prepare($value));

        if ($encoded === false) {
            return '';
        }

        return $encoded;
    }

    /**
     * Prepares value for encoding
     *
     * @param $value
     *
     * @return array|mixed|string
     */
    private function prepare($value)
    {
        if (!is_object($value) && (is_array($value) || is_iterable($value))) {
            $result = [];
            foreach ($value as $key => $subValue) {
                if (is_array($subValue) || is_iterable($subValue)) {
                    $result[$key] = $this->prepare($subValue);
                } else {
                    $result[$key] = $this->transform($subValue);
                }
            }

            return $result;
        } else {
            return $this->transform($value);
        }
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
