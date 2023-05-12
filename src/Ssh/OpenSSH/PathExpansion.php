<?php

declare(strict_types=1);

namespace Ssh\OpenSSH;

use Ssh\Environment;
use UnexpectedValueException;

use function str_starts_with;
use function substr;

trait PathExpansion
{
    private Environment|null $environment = null;

    /**
     * Replaces '~/' with users home path
     */
    private function expandPath(string $path): string
    {
        if (!str_starts_with($path, '~/')) {
            return $path;
        }

        $env = $this->environment ?? Environment::system();
        $home = $env->env['HOME'] ?? '';

        if ($home === '') {
            throw new UnexpectedValueException('Could not read HOME directory from environment');
        }

        return $home . substr($path, 1);
    }
}
