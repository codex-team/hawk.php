<?php

declare(strict_types=1);

namespace Hawk;

/**
 * Class Options is responsible for configuring the Hawk catcher.
 */
class Options
{
    /**
     * @var string
     */
    private $integrationToken = '';

    /**
     * @var string
     */
    private $url = 'https://k1.hawk.so/';

    /**
     * @var string
     */
    private $release = '';

    /**
     * @var int|null
     */
    private $errorTypes = null;

    /**
     * @var bool
     */
    private $captureSilencedErrors = false;

    /**
     * @var callable|null
     */
    private $beforeSend = null;

    /**
     * @var int
     */
    private $timeout = 10;

    /**
     * Map of accepted option keys to class properties.
     */
    private const OPTION_KEYS = [
        'integrationToken' => 'integrationToken',
        'integration_token' => 'integrationToken',
        'url' => 'url',
        'release' => 'release',
        'errorTypes' => 'errorTypes',
        'error_types' => 'errorTypes',
        'captureSilencedErrors' => 'captureSilencedErrors',
        'capture_silenced_errors' => 'captureSilencedErrors',
        'beforeSend' => 'beforeSend',
        'before_send' => 'beforeSend',
        'timeout' => 'timeout',
    ];

    /**
     * Options constructor.
     *
     * @param array $options Associative array of options to initialize.
     */
    public function __construct(array $options = [])
    {
        foreach ($options as $key => $value) {
            $normalizedKey = self::OPTION_KEYS[$key] ?? null;

            if ($normalizedKey === null) {
                throw new \InvalidArgumentException("Unknown option: $key");
            }

            $this->setOption($normalizedKey, $value);
        }
    }

    /**
     * Set a class property based on the normalized option key.
     *
     * @param string $key
     * @param mixed  $value
     */
    private function setOption(string $key, $value): void
    {
        switch ($key) {
            case 'integrationToken':
            case 'release':
            case 'url':
                if (!is_string($value)) {
                    throw new \InvalidArgumentException("Option '$key' must be a string.");
                }
                $this->$key = $value;

                break;

            case 'errorTypes':
                if (!is_int($value) && $value !== null) {
                    throw new \InvalidArgumentException("Option 'errorTypes' must be an integer or null.");
                }
                $this->errorTypes = $value;

                break;

            case 'captureSilencedErrors':
                if (!is_bool($value)) {
                    throw new \InvalidArgumentException("Option 'captureSilencedErrors' must be a boolean.");
                }
                $this->captureSilencedErrors = $value;

                break;

            case 'beforeSend':
                if (!is_callable($value) && $value !== null) {
                    throw new \InvalidArgumentException("Option 'beforeSend' must be callable or null.");
                }
                $this->beforeSend = $value;

                break;

            case 'timeout':
                if (!is_int($value)) {
                    throw new \InvalidArgumentException("Option 'timeout' must be an integer.");
                }
                $this->timeout = $value;

                break;

            default:
                throw new \InvalidArgumentException("Unknown option '$key'.");
        }
    }

    public function getIntegrationToken(): string
    {
        return $this->integrationToken;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getRelease(): string
    {
        return $this->release;
    }

    public function getErrorTypes(): int
    {
        return $this->errorTypes ?? error_reporting();
    }

    public function shouldCaptureSilencedErrors(): bool
    {
        return $this->captureSilencedErrors;
    }

    public function getBeforeSend(): ?callable
    {
        return $this->beforeSend;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }
}
