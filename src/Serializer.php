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

            $arrayValues = [];
            foreach ($value as $val) {
                $arrayValues[] = $this->serializeValue($val);
            }

            $value = implode(',', $arrayValues);
        } elseif (is_bool($value)) {
            $value = $value === true ? 'true' : 'false';
        } elseif (is_null($value)) {
            $value = 'null';
        } else {
            $value = (string) $value;
        }

        return $value;
    }
}
