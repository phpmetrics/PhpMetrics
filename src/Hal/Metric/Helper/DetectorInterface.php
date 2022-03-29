<?php
declare(strict_types=1);

namespace Hal\Metric\Helper;

use PhpParser\Node;

/**
 * This interface provides a method to infer some complex information about a given node.
 */
interface DetectorInterface
{
    /**
     * Returns the string referencing what is intended to be detected, or NULL if the kind does not match.
     *
     * @param Node $node
     * @return string|null
     */
    public function detects(Node $node): null|string;
}
