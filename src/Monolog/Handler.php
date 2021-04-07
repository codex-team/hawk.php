<?php

declare(strict_types=1);

namespace Hawk\Monolog;

use Hawk\Catcher;
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

        if (isset($record['context']['exception']) && $record['context']['exception'] instanceof \Throwable) {
            $exception = $record['context']['exception'];
        }

        $context = [];
        if (!empty($record['context'])) {
            $context = $record['context'];
        }

        if ($exception !== null) {
            $catcher->sendException($exception, $context);
        }
    }
}
