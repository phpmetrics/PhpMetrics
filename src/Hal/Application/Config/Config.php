<?php
declare(strict_types=1);

namespace Hal\Application\Config;

use function array_key_exists;

/**
 * Registry of the configuration used for the current PhpMetrics run.
 */
final class Config implements ConfigBagInterface
{
    /** @var array<string, mixed> */
    private array $bag = [];

    public function set(string $key, mixed $value): void
    {
        $this->bag[$key] = $value;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->bag);
    }

    public function get(string $key): mixed
    {
        return $this->has($key) ? $this->bag[$key] : null;
    }

    /**
     * {@inheritDoc}
     */
    public function all(): array
    {
        return $this->bag;
    }
}
