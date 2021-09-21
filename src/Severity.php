<?php

declare(strict_types=1);

namespace Hawk;

class Severity
{
    public const DEBUG = 'debug';
    public const INFO = 'info';
    public const WARNING = 'warning';
    public const ERROR = 'error';
    public const FATAL = 'fatal';

    /**
     * @var string
     */
    private $value;

    /**
     * Constructor.
     *
     * @param string $value
     */
    public function __construct(string $value)
    {
        $this->validate($value);
        $this->value = $value;
    }

    /**
     * Translate a PHP Error constant into string.
     *
     * @param int $severity
     *
     * @return \Hawk\Severity
     */
    public static function fromError(int $severity): self
    {
        $warnings = [
            \E_DEPRECATED,
            \E_USER_DEPRECATED,
            \E_WARNING,
            \E_USER_WARNING,
        ];

        if (in_array($severity, $warnings)) {
            return self::warning();
        }

        $fatals = [
            \E_ERROR,
            \E_PARSE,
            \E_CORE_ERROR,
            \E_CORE_WARNING,
            \E_COMPILE_ERROR,
            \E_COMPILE_WARNING,
        ];

        if (in_array($severity, $fatals)) {
            return self::fatal();
        }

        $errors = [
            \E_RECOVERABLE_ERROR,
            \E_USER_ERROR,
        ];

        if (in_array($severity, $errors)) {
            return self::error();
        }

        $infos = [
            \E_NOTICE,
            \E_USER_NOTICE,
            \E_STRICT,
        ];

        if (in_array($severity, $infos)) {
            return self::info();
        }

        return self::error();
    }

    /**
     * Creates a new instance with "debug" value.
     */
    public static function debug(): self
    {
        return new self(self::DEBUG);
    }

    /**
     * Creates a new instance with "info" value.
     */
    public static function info(): self
    {
        return new self(self::INFO);
    }

    /**
     * Creates a new instance with "warning" value.
     */
    public static function warning(): self
    {
        return new self(self::WARNING);
    }

    /**
     * Creates a new instance with "error" value.
     */
    public static function error(): self
    {
        return new self(self::ERROR);
    }

    /**
     * Creates a new instance with "fatal" value.
     */
    public static function fatal(): self
    {
        return new self(self::FATAL);
    }

    /**
     * Returns severity value as string
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param int $value
     */
    private function validate(string $value)
    {
        $validErrorTypes = [
            self::DEBUG,
            self::ERROR,
            self::FATAL,
            self::WARNING,
            self::INFO
        ];

        if (!\in_array($value, $validErrorTypes, true)) {
            throw new \InvalidArgumentException(sprintf('The "%s" is not a valid severity value.', $value));
        }
    }
}
