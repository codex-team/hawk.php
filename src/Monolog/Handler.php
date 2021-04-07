<?php

declare(strict_types=1);

namespace Hawk\Monolog;

use Hawk\Catcher;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\AbstractProcessingHandler;

/**
 * Class Handler
 *
 * @package Hawk\Monolog
 */
class Handler extends AbstractProcessingHandler
{
    /**
     * @inheritDoc
     */
    public function write(array $record): void
    {
        $catcher = Catcher::get();

        $data = [
            'level' => $record['level'],
            'title' => (new LineFormatter('%message%'))->format($record)
        ];

        if (isset($record['context']['exception']) && $record['context']['exception'] instanceof \Throwable) {
            $data['exception'] = $record['context']['exception'];
        }

        $catcher->sendEvent($data);
    }
}
