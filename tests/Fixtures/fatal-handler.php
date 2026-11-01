<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use Hawk\Event;
use Hawk\EventPayloadBuilder;
use Hawk\Handler;
use Hawk\Options;
use Hawk\Serializer;
use Hawk\StacktraceFrameBuilder;
use Hawk\Transport\TransportInterface;

$transport = new class implements TransportInterface {
    public function getUrl(): string
    {
        return '';
    }

    public function send(Event $event)
    {
        echo "HAWK_EVENT\n";
    }
};

$handler = new Handler(
    new Options(['integrationToken' => 'test-token']),
    $transport,
    new EventPayloadBuilder(new StacktraceFrameBuilder(new Serializer()))
);
$handler->registerFatalHandler();

if ($argv[1] === 'exception') {
    $handler->registerExceptionHandler();

    throw new RuntimeException('Unhandled application exception');
}

if ($argv[1] === 'fatal') {
    $handler->registerErrorHandler();
    $handler->registerExceptionHandler();
    eval('function hawk_duplicate_probe() {} function hawk_duplicate_probe() {}');
}

trigger_error('Non-fatal warning', E_USER_WARNING);
