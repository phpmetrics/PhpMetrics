<?php

namespace Hal\Metric\Helper;

use PhpParser\Node;

/**
 * Performs
 */
class MetricClassNameGenerator
{
    /**
     * @param Node|Node\Stmt\Class_|Node\Stmt\Interface_ $node
     *
     * @return string
     */
    public static function getName(Node $node)
    {
        return ($node instanceof Node\Stmt\Class_ && $node->isAnonymous()) ?
            'anonymous@' . spl_object_hash($node) :
            $node->namespacedName->toString();
    }
}
