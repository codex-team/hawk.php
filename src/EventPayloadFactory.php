<?php

declare(strict_types=1);

namespace Hawk;

use Hawk\Addons\AddonInterface;
use Hawk\Addons\Headers;
use Hawk\Addons\Os;
use Hawk\Addons\Runtime;
use Hawk\Util\Stacktrace;

/**
 * Class EventPayloadFactory is a factory object
 *
 * @package Hawk
 */
class EventPayloadFactory
{
    /**
     * List of addon resolvers
     *
     * @var array
     */
    private $addonsResolvers = [];

    /**
     * @var array
     */
    private $user;

    /**
     * @var array
     */
    private $context;

    /**
     * EventPayloadFactory constructor.
     *
     * @param array $user
     * @param array $context
     */
    public function __construct(array $user, array $context)
    {
        $this->user = $user;
        $this->context = $context;

        $this->addonsResolvers['runtime'] = new Runtime();
        $this->addonsResolvers['server'] = new Os();
        $this->addonsResolvers['header'] = new Headers();
    }

    /**
     * Returns EventPayload object
     *
     * @param array $data - event payload
     *
     * @return EventPayload
     */
    public function create(array $data): EventPayload
    {
        $eventPayload = new EventPayload();

        if (isset($data['context'])) {
            $eventPayload->setContext(array_merge($this->context, $data['context']));
        } else {
            $eventPayload->setContext($this->context);
        }

        $eventPayload->setUser($this->user);

        if (isset($data['exception']) && $data['exception'] instanceof \Throwable) {
            $exception = $data['exception'];
            $stacktrace = Stacktrace::buildStack($exception);

            $eventPayload->setTitle($exception->getMessage());
        } else {
            $stacktrace = debug_backtrace();
        }

        $eventPayload->setBacktrace($stacktrace);

        // Resolve addons
        $eventPayload->setAddons($this->resolveAddons());

        return $eventPayload;
    }

    /**
     * Resolves addons list and returns array
     *
     * @return array
     */
    private function resolveAddons(): array
    {
        $result = [];

        /**
         * @var string         $key
         * @var AddonInterface $resolver
         */
        foreach ($this->addonsResolvers as $key => $resolver) {
            $result[$key] = $resolver->resolve();
        }

        return $result;
    }
}
