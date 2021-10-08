<?php

declare(strict_types=1);

namespace Hawk;

use Hawk\Addons\AddonInterface;

/**
 * Class EventPayloadBuilder is a factory object
 *
 * @package Hawk
 */
class EventPayloadBuilder
{
    /**
     * List of addon resolvers
     *
     * @var array
     */
    private $addonsResolvers = [];

    /**
     * StacktraceFrameBuilder object. Used to parse exception stacktrace
     *
     * @var StacktraceFrameBuilder
     */
    private $stacktraceFrameBuilder;

    /**
     * EventPayloadFactory constructor.
     */
    public function __construct(StacktraceFrameBuilder $stacktraceFrameBuilder)
    {
        $this->stacktraceFrameBuilder = $stacktraceFrameBuilder;
    }

    /**
     * Adds addon resolver to the list
     *
     * @param AddonInterface $addon
     *
     * @return $this
     */
    public function registerAddon(AddonInterface $addon): self
    {
        $this->addonsResolvers[] = $addon;

        return $this;
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

        $eventPayload->setContext($data['context']);
        $eventPayload->setUser($data['user']);

        if (isset($data['exception']) && $data['exception'] instanceof \Throwable) {
            $exception = $data['exception'];
            $stacktrace = $this->stacktraceFrameBuilder->buildStack($exception);

            $eventPayload->setTitle($exception->getMessage());
        } else {
            $stacktrace = debug_backtrace();
        }

        if (isset($data['type'])) {
            $eventPayload->setType($data['type']);
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
            $result[$resolver->getName()] = $resolver->resolve();
        }

        return $result;
    }
}
