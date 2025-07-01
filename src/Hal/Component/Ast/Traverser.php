<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Ast;

use PhpParser\Node;
use PhpParser\NodeTraverser as Mother;
use PhpParser\NodeVisitor;

/**
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 * @internal
 */
class Traverser
{
    /**
     * @var callable
     */
    protected $stopCondition;

    /** @var Mother */
    private $traverser;

    /**
     * @param Mother $traverser
     * @param callable|null $stopCondition
     */
    public function __construct(Mother $traverser, $stopCondition = null)
    {
        if (null === $stopCondition) {
            $stopCondition = function ($node) {
                if ($node instanceof Node\Stmt\Class_ || $node instanceof Node\Stmt\Interface_) {
                    return false;
                }

                return true;
            };
        }

        $this->stopCondition = $stopCondition;
        $this->traverser = $traverser;
    }

    /**
     * @param array $nodes
     * @param NodeVisitor[] $visitors
     * @return array
     */
    public function traverseArray(array $nodes, array $visitors)
    {
        $doNodes = [];

        foreach ($nodes as $i => &$node) {
            if (is_array($node)) {
                $node = $this->traverseArray($node, $visitors);
            } elseif ($node instanceof Node) {
                $traverseChildren = call_user_func($this->stopCondition, $node);

                foreach ($visitors as $visitor) {
                    $return = $visitor->enterNode($node);
                    if (Mother::DONT_TRAVERSE_CHILDREN === $return) {
                        $traverseChildren = false;
                    } elseif (null !== $return) {
                        $node = $return;
                    }
                }

                if ($traverseChildren) {
                    $node = $this->traverser->traverseNode($node);
                }

                foreach ($visitors as $visitor) {
                    $return = $visitor->leaveNode($node);

                    if (Mother::REMOVE_NODE === $return) {
                        $doNodes[] = [$i, []];
                        break;
                    } elseif (is_array($return)) {
                        $doNodes[] = [$i, $return];
                        break;
                    } elseif (null !== $return) {
                        $node = $return;
                    }
                }
            }
        }

        if (!empty($doNodes)) {
            while (list($i, $replace) = array_pop($doNodes)) {
                array_splice($nodes, $i, 1, $replace);
            }
        }

        return $nodes;
    }
}
