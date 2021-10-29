<?php

declare(strict_types=1);

namespace Hawk\Tests\Unit;

use Hawk\Serializer;
use PHPUnit\Framework\TestCase;

class SerializerTest extends TestCase
{
    /**
     * @dataProvider valueProvider
     *
     * @param mixed $testCase
     */
    public function testSerializationResult($testCase)
    {
        $fixture = new Serializer();
        $result = $fixture->serializeValue($testCase['value']);

        $this->assertEquals($testCase['expect'], $result);
    }

    /**
     * Returns list of test cases
     *
     * @return array
     */
    public function valueProvider(): array
    {
        return [
            [
                [
                    'value' => 9999,
                    'expect' => '9999'
                ]
            ],
            [
                [
                    'value' => true,
                    'expect' => 'true'
                ]
            ],
            [
                [
                    'value' => 'val',
                    'expect' => 'val'
                ]
            ],
            [
                [
                    'value' => [1, 2, 3, 4, 5],
                    'expect' => '[1,2,3,4,5]'
                ]
            ],
            [
                [
                    'value' => ['string', 1, true],
                    'expect' => '["string",1,"true"]'
                ]
            ],
            [
                [
                    'value' => [function () {
                    }, \Closure::class],
                    'expect' => '["Closure","Closure"]'
                ]
            ],
            [
                [
                    'value' => new \stdClass(),
                    'expect' => 'stdClass'
                ]
            ],
            [
                [
                    'value' => [1, new \stdClass(), new \Exception()],
                    'expect' => '[1,"stdClass","Exception"]'
                ]
            ],
            [
                [
                    'value' => [[1, 2, 3], 'something', [function () {
                    }, [new \stdClass()]]],
                    'expect' => '["[1,2,3]","something","[\"Closure\",\"[\\\\\"stdClass\\\\\"]\"]"]'
                ]
            ],
            [
                [
                    'value' => null,
                    'expect' => 'null'
                ]
            ],
            [
                [
                    'value' => [new \ArrayIterator([1, 2, 3])],
                    'expect' => '["ArrayIterator"]'
                ]
            ],
            [
                [
                    'value' => new \CachingIterator(new \ArrayIterator()),
                    'expect' => 'CachingIterator'
                ]
            ],
            [
                [
                    'value' => ['key1' => 'value1', 'key2' => 'value2'],
                    'expect' => '{"key1":"value1","key2":"value2"}'
                ]
            ]
        ];
    }
}
