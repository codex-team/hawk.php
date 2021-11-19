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

    public function testSerializationWithMediumSizeArray(): void
    {
        $mediumArray = [];
        $this->fillArray($mediumArray, 1, 20);

        $fixture = new Serializer();
        $result = $fixture->serializeValue($mediumArray);

        $this->assertEquals('{"1":{"2":{"3":{"4":{"5":{"6":{"7":{"8":{"9":{"10":{"11":{"12":{"13":{"14":{"15":{"16":{"17":{"18":{"19":[]}}}}}}}}}}}}}}}}}}}', $result);
    }

    public function testSerializationWithLargeArray(): void
    {
        // MaxDepth is 1000
        $largeArray = [];
        $this->fillArray($largeArray);

        $fixture = new Serializer();

        // json_encode will return false and result is empty string
        $result = $fixture->serializeValue($largeArray);

        $this->assertEquals("", trim($result));
    }

    /**
     * Fills empty array with values:
     *   [1 => [2 => [3 => ....]]]
     *
     * @param array $array
     * @param int   $currentDepth
     * @param int   $maxDepth
     */
    private function fillArray(array &$array, int $currentDepth = 0, int $maxDepth = 1000)
    {
        if ($currentDepth === $maxDepth) {
            return;
        }

        $array[$currentDepth] = [];
        $this->fillArray($array[$currentDepth], $currentDepth + 1, $maxDepth);
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
                    'expect' => '"val"'
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
                    'expect' => '["string",1,true]'
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
                    'expect' => '"stdClass"'
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
                    'expect' => '[[1,2,3],"something",["Closure",["stdClass"]]]'
                ]
            ],
            [
                [
                    'value' => null,
                    'expect' => '"null"'
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
                    'expect' => '"CachingIterator"'
                ]
            ],
            [
                [
                    'value' => ['key1' => 'value1', 'key2' => 'value2'],
                    'expect' => '{"key1":"value1","key2":"value2"}'
                ]
            ],
            [
                [
                    'value' => urldecode('bad utf string %C4_'),
                    'expect' => ''
                ]
            ],
        ];
    }
}
