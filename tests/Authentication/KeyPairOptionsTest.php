<?php declare(strict_types=1);
/**
 * @author    Axel Helmert <ah@luka.de>
 * @license   MIT
 * @copyright Copyright (c) 2024 LUKA netconsult GmbH (www.luka.de)
 */

namespace SshTest\Authentication;

use Ssh\Authentication\KeyPair;
use Ssh\Authentication\KeyPairOptions;
use PHPUnit\Framework\TestCase;

use function array_map;
use function iterator_to_array;

class KeyPairOptionsTest extends TestCase
{
    public function testShouldBuildFromDirectory(): void
    {
        $options = KeyPairOptions::fromDirectory(__DIR__ . '/assets/key_options');
        $keys = array_map(
            static fn (KeyPair $keyPair): string => $keyPair->privateKeyFile,
            iterator_to_array($options, false)
        );

        self::assertInstanceOf(KeyPairOptions::class, $options);
        self::assertCount(2, $options);
        self::assertContains(__DIR__ . '/assets/key_options/id_ecdsa', $keys);
        self::assertContains(__DIR__ . '/assets/key_options/id_rsa', $keys);
    }
}
