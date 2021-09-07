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
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'headers';
    }

    /**
     * @inheritDoc
     */
    public function resolve(): array
    {
        if (function_exists('getallheaders')) {
            $result = getallheaders();
        } else {
            $result = [];
            foreach ($_SERVER as $key => $value) {
                if (substr($key, 0, 5) == 'HTTP_') {
                    $key = str_replace(
                        ' ',
                        '-',
                        ucwords(strtolower(str_replace('_', ' ', substr($key, 5))))
                    );
                    $result[$key] = $value;
                } else {
                    $result[$key] = $value;
                }
            }
        }

        return $result;
    }
}
