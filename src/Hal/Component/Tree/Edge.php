<?php
declare(strict_types=1);

namespace Hal\Component\Tree;

use Stringable;
use function sprintf;

/**
 * Represents an edge in a graph, linking 2 nodes.
 */
final class Edge implements Stringable
{
    public bool $cyclic = false;

    public function __construct(
        private readonly Node $from,
        private readonly Node $to
    ) {
    }

    public function getFrom(): Node
    {
        return $this->from;
    }

    public function getTo(): Node
    {
        return $this->to;
    }

    public function __toString(): string
    {
        return sprintf('%s ➔ %s', $this->from, $this->to);
    }
}
