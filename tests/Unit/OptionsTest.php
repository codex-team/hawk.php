<?php

declare(strict_types=1);

namespace Hawk\Tests\Unit;

use Hawk\Options;
use Hawk\Transport;
use PHPUnit\Framework\TestCase;

class OptionsTest extends TestCase
{
    public function testDefaultOptions(): void
    {
        $options = new Options();

        $this->assertNull($options->getBeforeSend());
        $this->assertEmpty($options->getIntegrationToken());
        $this->assertEmpty($options->getRelease());
        $this->assertEquals('https://k1.hawk.so/', $options->getUrl());
        $this->assertEquals(2, $options->getTimeout());
        $this->assertEquals('curl', $options->getTransport());
        $this->assertEquals(error_reporting(), $options->getErrorTypes());
        $this->assertArrayHasKey($options->getTransport(), Transport::getTransports());
    }

    public function testCustomOptions(): void
    {
        $config = [
            'url' => 'www.mysite.com',
            'integrationToken' => 'myToken',
            'release' => '123',
            'error_types' => 11,
            'beforeSend' => function () {},
            'timeout' => 60,
            'transport' => 'guzzle',
        ];

        $options = new Options($config);
        $this->assertSame($config, [
            'url' => $options->getUrl(),
            'integrationToken' => $options->getIntegrationToken(),
            'release' => $options->getRelease(),
            'error_types' => $options->getErrorTypes(),
            'beforeSend' => $options->getBeforeSend(),
            'timeout' => $options->getTimeout(),
            'transport' => $options->getTransport(),
        ]);
    }
}
