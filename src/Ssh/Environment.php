<?php

declare(strict_types=1);

namespace Ssh;

use function getenv;
use function in_array;
use function str_starts_with;
use function strtolower;
use function substr;

final class Environment
{
    private static Environment|null $system = null;

    /**
     * @param array<array-key, string> $env
     */
    public function __construct(public readonly array $env)
    {
    }

    public static function system(): self
    {
        if (!self::$system) {
            self::$system = new self(getenv());
        }

        return self::$system;
    }

    public static function get(string $key): string|null
    {
        return self::system()->env[$key] ?? null;
    }

    /**
     * Resolve SSH related file path
     *
     * When the given path is relative, it will be resolved relative to
     * the user's local .ssh directory.
     */
    public function resolveSshFile(string $filename): string
    {
        if (str_starts_with($filename, '/')) {
            return $filename;
        }

        $home = $this->env['HOME'] ?? '';

        if (str_starts_with($filename, '~/')) {
            return $home . substr($filename, 1);
        }

        return $home . '/.ssh/' . $filename;
    }

    public function flag(string $key, bool $default = false): bool
    {
        $value = $this->env[$key] ?? null;

        if ($value === null) {
            return $default;
        }

        return !in_array(
            strtolower($value),
            ['false', '0', '', 'no', 'off'],
            true,
        );
    }
}
