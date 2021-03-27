<?php

declare(strict_types=1);

namespace Hawk\Addons;

/**
 * Class Headers
 *
 * @package Hawk\Addons
 */
class Headers implements AddonInterface
{
    /**
     * @var string[]
     */
    private $fields = [
        'DOCUMENT_ROOT',
        'REMOTE_ADDR',
        'REMOTE_PORT',
        'SERVER_PROTOCOL',
        'SERVER_NAME',
        'SERVER_PORT',
        'HTTP_CONNECTION',
        'HTTP_CACHE_CONTROL',
        'HTTP_USER_AGENT',
        'HTTP_ACCEPT',
        'QUERY_STRING'
    ];

    /**
     * @return array
     */
    public function resolve(): array
    {
        $result = [];
        foreach ($this->fields as $field) {
            $result[$field] = $_SERVER[$field] ?? '';
        }

        return $result;
    }
}
