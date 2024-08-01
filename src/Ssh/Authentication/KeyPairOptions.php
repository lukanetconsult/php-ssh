<?php declare(strict_types=1);
/**
 * @author    Axel Helmert <ah@luka.de>
 * @license   MIT
 * @copyright Copyright (c) 2023 LUKA netconsult GmbH (www.luka.de)
 */

namespace Ssh\Authentication;

use ArrayIterator;
use CallbackFilterIterator;
use Countable;
use DirectoryIterator;
use FilesystemIterator;
use IteratorAggregate;
use SplFileInfo;
use Traversable;

use function array_values;
use function getenv;
use function is_string;
use function iterator_count;
use function str_ends_with;
use function str_starts_with;

/**
 * @implements IteratorAggregate<KeyPair>
 */
final readonly class KeyPairOptions implements IteratorAggregate, Countable
{
    /**
     * @var list<KeyPair>
     */
    public array $options;

    public function __construct(KeyPair ...$options)
    {
        $this->options = array_values($options);
    }

    public static function fromFilename(string|null $file): self
    {
        return self::fromKeyPair($file !== null ? new KeyPair($file) : null);
    }

    public static function fromKeyPair(KeyPair|null $keyPair): self
    {
        if ($keyPair !== null) {
            return new self($keyPair);
        }

        $home = getenv('HOME');
        $path = is_string($home) ? $home : '';
        $path .= '/.ssh';

        return new self(
            new KeyPair($path . '/id_rsa'),
            new KeyPair($path . '/id_ecdsa'),
            new KeyPair($path . '/id_dsa'),
        );
    }

    /**
     * Scans the given directory for private/public key pairs
     */
    public static function fromDirectory(string $directory): self
    {
        $iterator = new FilesystemIterator($directory, FilesystemIterator::SKIP_DOTS | FilesystemIterator::CURRENT_AS_FILEINFO);
        $options = [];

        foreach ($iterator as $entry) {
            assert($entry instanceof SplFileInfo);
            $filename = $entry->getFilename();

            if ($entry->isFile() && !$entry->isDir() && str_starts_with($filename, 'id_') && !str_ends_with($filename, '.pub')) {
                $options[] = new KeyPair($entry->getPathname());
            }
        }

        return new self(...$options);
    }

    /**
     * @return Traversable<KeyPair>
     */
    public function getIterator(): Traversable
    {
        return new CallbackFilterIterator(
            new ArrayIterator($this->options),
            static fn (KeyPair $current) => $current->exists(),
        );
    }

    public function count(): int
    {
        return iterator_count($this->getIterator());
    }
}
