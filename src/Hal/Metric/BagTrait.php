<?php
declare(strict_types=1);

namespace Hal\Metric;

use function array_key_exists;

/**
 * This bag can store a list of metrics values.
 */
trait BagTrait
{
    /** @var array<string, mixed> */
    private array $bag;

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->bag = ['name' => $name];
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        /** @var string As the name given is a string. */
        return $this->bag['name'];
    }

    /**
     * {@inheritDoc}
     */
    public function set(string $key, mixed $value): void
    {
        $this->bag[$key] = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->bag);
    }

    /**
     * {@inheritDoc}
     */
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

    /**
     * {@inheritDoc}
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [...$this->all(), '_type' => $this::class];
    }
}
