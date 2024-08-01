<?php

declare(strict_types=1);

namespace Ssh\OpenSSH;

use Ssh\Authentication;
use Ssh\Configuration;
use Ssh\Environment;
use Ssh\HostConfiguration;
use Ssh\ProvidesAuthentication;

use function dirname;
use function is_dir;

final class ConfigFile implements Configuration, ProvidesAuthentication
{
    use ConfigDecoratorTrait;
    use PathExpansion;

    public const DEFAULT_SSH_CONFIG = 'config';

    private HostConfig $hostConfig;
    private Authentication\KeyPairOptions $defaultKeyOptions;

    /**
     * @var array<string, array<string, string>>
     */
    private array $data;

    public function __construct(
        Configuration $hostConfig,
        string|null $file = null,
        Environment $environment = null,
        Authentication\KeyPairOptions|null $defaultKeyOptions = null,
    ) {
        $this->environment = $environment ?? Environment::system();
        $file = $file ?? $this->environment->env['SSH_CONFIG'] ?? self::DEFAULT_SSH_CONFIG;
        $file = $this->environment->resolveSshFile($file);

        $this->defaultKeyOptions = $defaultKeyOptions ?? $this->defaultKeyOptionsFromConfigFile($file);
        $this->data = (new Parser())->parse($file);
        $this->hostConfig = $this->findConfig($hostConfig);
        $this->decoratedConfig = $this->hostConfig;
    }

    public static function forHostname(string $hostname, string|null $file = null): self
    {
        return new self(new HostConfiguration($hostname), $file);
    }

    private function defaultKeyOptionsFromConfigFile(string $filePath): Authentication\KeyPairOptions
    {
        $keyPath = $this->environment->env['SSH_KEYS_DIR'] ?? dirname($filePath);
        return Authentication\KeyPairOptions::fromDirectory($keyPath);
    }

    private function prepareIdFile(string|null $path): Authentication\KeyPairOptions
    {
        if ($path === '') {
            $path = null;
        }

        return $path === null
            ? $this->defaultKeyOptions
            : Authentication\KeyPairOptions::fromFilename($this->expandPath($path));
    }

    private function findConfig(Configuration $config): HostConfig
    {
        $matches = array_filter($this->data, fn (array $configData): bool => fnmatch($configData['host'], $config->getHost()));
        usort($matches, fn (array $a, array $b): int => strlen($a['host']) <=> strlen($b['host']));
        $result = (count($matches) > 1)? array_merge(...$matches) : ($matches[0] ?? []);

        return new HostConfig(
            new HostConfiguration(
                $result['hostname'] ?? $config->getHost(),
                intval($result['port'] ?? $config->getPort()),
                $config->getMethods(),
                $config->getCallbacks()
            ),
            $result['user'] ?? null,
            $this->prepareIdFile($result['identityfile'] ?? null)
        );
    }

    /**
     * Builds a configuration for the given host
     */
    public function forHost(string|Configuration $host): HostConfig
    {
        return $this->findConfig(
            $host instanceof Configuration
                ? $host
                : new HostConfiguration($host)
        );
    }

    public function getUser(): string|null
    {
        return $this->hostConfig->getUser();
    }

    public function createAuthentication(string|null $passphrase = null, string|null $user = null): Authentication
    {
        return $this->hostConfig->createAuthentication($passphrase, $user);
    }
}
