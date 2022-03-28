<?php
declare(strict_types=1);

namespace Hal\Metric\Helper;

use PhpParser\Node;
use function property_exists;
use function spl_object_hash;

/**
 * Provides helpers that infer a name of a node according to the given node.
 */
final class MetricNameGenerator
{
    /**
     * Returns a calculated class name of node from the given node in argument.
     *
     * @param Node $node
     * @return string
     */
    public static function getClassName(Node $node): string
    {
        return match (true) {
            $node instanceof Node\Stmt\Class_ && $node->isAnonymous() => 'anonymous@' . spl_object_hash($node),
            property_exists($node, 'namespacedName') => $node->namespacedName->toString(),
            property_exists($node, 'name') => $node->name->toString(),
            default => 'unknown@' . spl_object_hash($node)
        };
    }

    /**
     * Returns a calculated function name of node from the given node in argument.
     *
     * @param Node $node
     * @return string
     */
    public static function getFunctionName(Node $node): string
    {
        return match (true) {
            property_exists($node, 'name') => $node->name->toString(),
            default => 'unknown@' . spl_object_hash($node)
        };
    }
}
