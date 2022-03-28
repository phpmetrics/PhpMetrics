<?php
declare(strict_types=1);

namespace Tests\Hal\Application\Config;

use Hal\Application\Config\Config;
use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{
    public function testConfigurationBag(): void
    {
        $config = new Config();

        // Bag is missing something? We add it, and it's there.
        self::assertFalse($config->has('foo'));
        $config->set('foo', 'FOO');
        self::assertTrue($config->has('foo'));
        self::assertSame('FOO', $config->get('foo'));

        // ... Also works when we decided to add `null`.
        self::assertFalse($config->has('null'));
        $config->set('null', null);
        self::assertTrue($config->has('null'));
        self::assertNull($config->get('null'));

        // Checks that `null` is returned when the key is not in the bag.
        self::assertFalse($config->has('no_key'));
        self::assertNull($config->get('null'));

        // Checks a key can be rewritten.
        $config->set('rewrite', 7);
        self::assertSame(7, $config->get('rewrite'));
        $config->set('rewrite', 8);
        self::assertNotSame(7, $config->get('rewrite'));
        self::assertSame(8, $config->get('rewrite'));

        // Check all keys defined in the config bag.
        $expectedKeys = [
            'foo' => 'FOO',
            'null' => null,
            'rewrite' => 8
        ];
        self::assertSame($expectedKeys, $config->all());
    }
}
