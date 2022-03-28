<?php
declare(strict_types=1);

namespace Hal\Application\Config;

/**
 * Defines accesses to any configuration key/value.
 */
interface ConfigBagInterface
{
    /**
     * Set any value to the given key in the configuration bag.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $key, mixed $value): void;

    /**
     * Check if a key exists in the configuration bag.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Fetch the value associated to the requested key.
     *
     * @param string $key
     * @return mixed
     */
    public function get(string $key): mixed;

    /**
     * Return the whole bag content.
     *
     * @return array<string, mixed>
     */
    public function all(): array;
}
