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

/**
 * Custom Ast Traverser
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class NodeTraverser extends Mother
{
    protected $stopCondition;

    /**
     * NodeTraverser constructor.
     *
     * @param bool $cloneNodes
     * @param null $stopCondition
     */
    public function __construct($cloneNodes = false, $stopCondition = null)
    {
        parent::__construct($cloneNodes);

        if (null === $stopCondition) {
            $stopCondition = function ($node) {
                return !($node instanceof Node\Stmt\Class_ || $node instanceof Node\Stmt\Interface_);
            };
        }

        $this->stopCondition = $stopCondition;
    }

    /**
     * @param array $nodes
     * @return array
     */
    protected function traverseArray(array $nodes)
    {
        $doNodes = [];

        foreach ($nodes as $i => &$node) {
            if (\is_array($node)) {
                $node = $this->traverseArray($node);
            } elseif ($node instanceof Node) {
                $traverseChildren = \call_user_func($this->stopCondition, $node);

                foreach ($this->visitors as $visitor) {
                    $return = $visitor->enterNode($node);
                    if (self::DONT_TRAVERSE_CHILDREN === $return) {
                        $traverseChildren = false;
                    } elseif (null !== $return) {
                        $node = $return;
                    }
                }

                if ($traverseChildren) {
                    $node = $this->traverseNode($node);
                }

                foreach ($this->visitors as $visitor) {
                    $return = $visitor->leaveNode($node);

                    if (self::REMOVE_NODE === $return) {
                        $doNodes[] = [$i, []];
                        break;
                    }
                    if (\is_array($return)) {
                        $doNodes[] = [$i, $return];
                        break;
                    }
                    if (null !== $return) {
                        $node = $return;
                    }
                }
            }
        } unset($node);

        if (!empty($doNodes)) {
            while (list($i, $replace) = \array_pop($doNodes)) {
                \array_splice($nodes, $i, 1, $replace);
            }
        }

        return $nodes;
    }
}
