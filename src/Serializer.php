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
     * @return mixed
     */
    public function serializeValue($value)
    {
        if (is_object($value)) {
            $value = get_class($value);
        } elseif (is_iterable($value)) {
            if (!is_array($value)) {
                $value = iterator_to_array($value);
            }

            if ($this->isAssoc($value)) {
                $value = json_encode($value);
            } else {
                $arrayValues = [];
                foreach ($value as $val) {
                    $arrayValues[] = $this->serializeValue($val);
                }

                $value = json_encode($arrayValues);
            }
        } elseif (is_bool($value)) {
            $value = $value === true ? 'true' : 'false';
        } elseif (is_null($value)) {
            $value = 'null';
        }

        return $value;
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
