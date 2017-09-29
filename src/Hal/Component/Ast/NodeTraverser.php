<?php
/**
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Ast;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\NodeTraverser as Mother;
use PhpParser\NodeVisitor;

/**
 * Class NodeTraverser
 * Custom Ast Traverser based on nikic/php-parser node-traverser.
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class NodeTraverser extends Mother
{
    /** @var \Closure|null Closure used to detect when to stop traversing nodes. Default closure when not set. */
    protected $stopCondition;

    /**
     * NodeTraverser constructor.
     *
     * @param \Closure|null $stopCondition Closure used to detect when to stop traversing nodes. Defaults to NULL.
     */
    public function __construct($stopCondition = null)
    {
        if (null === $stopCondition) {
            $stopCondition = function (Node $node) {
                return !($node instanceof ClassLike);
            };
        }

        $this->stopCondition = $stopCondition;
    }

    /**
     * Traverses the array of Node to run all defined visitors for each node.
     * @param Node[] $nodes The list of Node to traverse.
     * @return array
     */
    protected function traverseArray(array $nodes)
    {
        $doNodes = [];

        foreach ($nodes as $i => &$node) {
            if (\is_array($node)) {
                $node = $this->traverseArray($node);
                continue;
            }
            if (!($node instanceof Node)) {
                //Ignore nodes that are not instances of Node. (fallback bugged case?)
                continue;
            }

            if ($this->enterVisitNode($node)) {
                $node = $this->traverseNode($node);
            }

            foreach ($this->visitors as $visitor) {
                $return = $visitor->leaveNode($node);
                // Force the return to be an empty array if set to flag REMOVE_NODE.
                $return = [$return, []][self::REMOVE_NODE === $return];

                if (\is_array($return)) {
                    $doNodes[] = [$i, $return];
                } elseif (null !== $return) {
                    $node = $return;
                }
            }
        } unset($node);

        // Replace input nodes by the new ones traversed.
        if (!empty($doNodes)) {
            while (list($i, $replace) = \array_pop($doNodes)) {
                \array_splice($nodes, $i, 1, $replace);
            }
        }

        return $nodes;
    }

    /**
     * Enter in the given node with each visitors to update the node and flag if we do not want to traverse the
     * children.
     * @param Node $node The node to visit with all visitors.
     * @return bool TRUE if we want to traverse the children classes, FALSE otherwise.
     */
    protected function enterVisitNode(Node $node)
    {
        $traverseChildren = \call_user_func($this->stopCondition, $node);

        foreach ($this->visitors as $visitor) {
            $return = $visitor->enterNode($node);
            if (self::DONT_TRAVERSE_CHILDREN === $return) {
                $traverseChildren = false;
            } elseif (null !== $return) {
                $node = $return;
            }
        }

        return $traverseChildren;
    }

    /**
     * Proxy to the parent addVisitor method to chain the calls.
     * @param NodeVisitor $visitor The visitor to add.
     * @return $this
     */
    public function addVisitor(NodeVisitor $visitor)
    {
        parent::addVisitor($visitor);
        return $this;
    }
}
