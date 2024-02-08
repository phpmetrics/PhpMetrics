<?php
declare(strict_types=1);

namespace Hal\Component\Ast;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\NodeTraverser as Mother;
use PhpParser\NodeVisitor;

use function array_splice;
use function is_array;

/**
 * Custom node traverser in order to not traverse children further than the CLassLike level.
 * Deeper traversing will be necessary on purpose, depending on the related metrics to calculate.
 */
final class NodeTraverser extends Mother
{
    /**
     * @param array<array-key, mixed> $nodes
     * @return array<int, mixed>
     */
    protected function traverseArray(array $nodes): array
    {
        $nodesToReplace = [];

        /** @var int $i Original definition must be changed. See: \PhpParser\NodeTraverser::traverseArray */
        foreach ($nodes as $i => &$node) {
            if (is_array($node)) {
                $node = $this->traverseArray($node);
                continue;
            }
            if (!($node instanceof Node)) {
                continue;
            }

            $traverseChildren = !($node instanceof ClassLike);

            $this->makeVisitorsEnterNode($node, $traverseChildren);
            if ($traverseChildren) {
                $this->traverseNode($node);
            }
            $this->makeVisitorsLeaveNode($node, $i, $nodesToReplace);
        } unset($node);

        foreach ($nodesToReplace as [$nodePosition, $replacement]) {
            array_splice($nodes, $nodePosition, 1, $replacement);
        }
        return $nodes;
    }

    /**
     * Make each visitor enter the given node.
     */
    private function makeVisitorsEnterNode(Node $node, bool &$traverseChildren): void
    {
        foreach ($this->visitors as $visitor) {
            $return = $visitor->enterNode($node);
            if (NodeVisitor::DONT_TRAVERSE_CHILDREN === $return) {
                $traverseChildren = false;
            } elseif ($return instanceof Node) {
                /** @var Node $return */
                $node = $return;
            }
        }
    }

    /**
     * Make each visitor leave the given node.
     *
     * @param Node $node
     * @param int $nodePosition
     * @param array<int, array{int, array<Node>}> $nodesToReplace
     */
    private function makeVisitorsLeaveNode(Node $node, int $nodePosition, array &$nodesToReplace): void
    {
        foreach ($this->visitors as $visitor) {
            $return = $visitor->leaveNode($node);

            if (NodeVisitor::REMOVE_NODE === $return) {
                $nodesToReplace[] = [$nodePosition, []];
                break;
            }
            if (is_array($return)) {
                $nodesToReplace[] = [$nodePosition, $return];
                break;
            }
            if ($return instanceof Node) {
                /** @var Node $return */
                $node = $return;
            }
        }
    }
}
