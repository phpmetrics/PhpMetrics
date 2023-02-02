<?php
declare(strict_types=1);

namespace Hal\Violation;

use ArrayIterator;
use function array_reduce;

/**
 * This class is holds all violations detected on the metric it is attached to.
 */
final class ViolationsHandler implements ViolationsHandlerInterface
{
    /** @var array<int, Violation> */
    private array $data = [];

    /**
     * {@inheritDoc}
     * @return ArrayIterator<int, Violation>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->data);
    }

    /**
     * {@inheritDoc}
     */
    public function getAll(): array
    {
        return $this->data;
    }

    /**
     * {@inheritDoc}
     */
    public function add(Violation $violation): void
    {
        $this->data[] = $violation;
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return array_reduce(
            $this->data,
            static fn (string $previous, Violation $violation): string => $previous . $violation->getName() . ',',
            ''
        );
    }
}
