<?php declare(strict_types=1);

namespace Ssh\Authentication;

use Ssh\Authentication;

/**
 * Username based authentication
 *
 * @author Antoine Hérault <antoine.herault@gmail.com>
 */
class None implements Authentication
{
    /**
     * @var string
     */
    protected $username;

    /**
     * Constructor
     */
    public function __construct(string $username)
    {
        $this->username = $username;
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate($session): bool
    {
        return (true === ssh2_auth_none($session, $this->username));
    }
}
