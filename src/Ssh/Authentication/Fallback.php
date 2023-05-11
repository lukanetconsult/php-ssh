<?php declare(strict_types=1);
/**
 * @author    Axel Helmert <ah@luka.de>
 * @license   MIT
 * @copyright Copyright (c) 2023 LUKA netconsult GmbH (www.luka.de)
 */

namespace Ssh\Authentication;

use Ssh\Authentication;
use Ssh\Session;

use function array_reduce;
use function array_values;

final readonly class Fallback implements Authentication
{
    public function __construct(
        private Authentication $primary,
        private Authentication $fallback
    ) {
    }

    public static function aggregate(Authentication $primary, Authentication ...$fallbacks): Authentication
    {
        return array_reduce(
            $fallbacks,
            static fn (Authentication $current, Authentication $fallback) => new self($current, $fallback),
            $primary,
        );
    }

    public function fallBackTo(Authentication $fallback): Authentication
    {
        return self::aggregate($this, $fallback);
    }

    /**
     * @inheritDoc
     */
    function authenticate(Session $session): bool
    {
        return $this->primary->authenticate($session)
            || $this->fallback->authenticate($session);
    }
}
