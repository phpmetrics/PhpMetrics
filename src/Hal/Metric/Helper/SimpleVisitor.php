<?php
declare(strict_types=1);

namespace Hal\Metric\Helper;

use Closure;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * Simple visitor that will execute the given callback on leaving node.
 */
final class SimpleVisitor extends NodeVisitorAbstract
{
    /**
     * @param Closure $callback
     */
    public function __construct(
        private readonly Closure $callback
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function leaveNode(Node $node): null|int|Node|array // TODO PHP 8.2: only return null here.
    {
        ($this->callback)($node);
        return null;
    }
}
