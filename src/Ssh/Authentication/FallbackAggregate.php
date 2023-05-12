<?php

declare(strict_types=1);

namespace Ssh\Authentication;

use Ssh\Authentication;

use Ssh\Session;

use function array_filter;
use function array_shift;
use function array_values;

final readonly class FallbackAggregate implements Authentication
{
    private Authentication|null $decorated;

    public function __construct(Authentication ...$options)
    {
        $primary = array_shift($options);
        $this->decorated = $primary
            ? Fallback::aggregate($primary, ...$options)
            : $primary;
    }

    public static function all(Authentication|null ...$options): self
    {
        return new self(...array_filter($options));
    }

    public function withFallback(Authentication $fallback): self
    {
        return $this->decorated
            ? new self($this->decorated, $fallback)
            : new self($fallback);
    }

    public function asFallbackFor(Authentication $primary): self
    {
        $fallbacks = $this->decorated ? [$this->decorated] : [];
        return new self($primary, ...$fallbacks);
    }

    public function authenticate(Session $session): bool
    {
        return $this->decorated?->authenticate($session) ?? false;
    }
}
