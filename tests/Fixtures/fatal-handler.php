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

$options = ['integrationToken' => 'test-token'];
if ($argv[1] === 'fatal-during-capture') {
    $options['beforeSend'] = function ($event) {
        static $triggerFatal = true;

        if ($triggerFatal) {
            $triggerFatal = false;
            eval('function hawk_duplicate_probe() {} function hawk_duplicate_probe() {}');
        }

        return $event;
    };
}

$handler = new Handler(
    new Options($options),
    $transport,
    new EventPayloadBuilder(new StacktraceFrameBuilder(new Serializer()))
);
$handler->registerFatalHandler();

if ($argv[1] === 'manual-then-fatal') {
    $handler->registerErrorHandler();
    $handler->registerExceptionHandler();

    try {
        $handler->handleException(new RuntimeException('Manually reported exception'));
    } catch (RuntimeException $exception) {
        // Continue to the fatal error after the application handles the exception.
    }

    eval('function hawk_duplicate_probe() {} function hawk_duplicate_probe() {}');
}

if ($argv[1] === 'exception') {
    $handler->registerExceptionHandler();

    throw new RuntimeException('Unhandled application exception');
}

if ($argv[1] === 'fatal') {
    $handler->registerErrorHandler();
    $handler->registerExceptionHandler();
    eval('function hawk_duplicate_probe() {} function hawk_duplicate_probe() {}');
}

if ($argv[1] === 'fatal-during-capture') {
    $handler->sendEvent(['title' => 'Before fatal']);
}

trigger_error('Non-fatal warning', E_USER_WARNING);
