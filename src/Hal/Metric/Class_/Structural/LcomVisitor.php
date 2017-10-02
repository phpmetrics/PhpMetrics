<?php
/**
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Metric\Class_\Structural;

use Hal\Component\Tree\GraphDeduplicated;
use Hal\Component\Tree\Node as TreeNode;
use Hal\Metric\Helper\MetricClassNameGenerator;
use Hal\Metric\Helper\NodeChecker;
use Hal\Metric\MetricsVisitorTrait;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;

/**
 * Class CyclomaticComplexityVisitor
 * Calculates the lack of cohesion of methods.
 *
 * @package Hal\Metric\Class_\Structural
 */
class LcomVisitor extends NodeVisitorAbstract
{
    use MetricsVisitorTrait;

    /**
     * Executed when leaving the traversing of the node. Used to calculates the following elements:
     * - Lack of cohesion of method
     * @param Node $node The current node to leave to make the analysis.
     * @return void
     */
    public function leaveNode(Node $node)
    {
        $class = $this->metrics->get(MetricClassNameGenerator::getName($node));

        if (!($node instanceof Stmt\Class_ || $node instanceof Stmt\Trait_) || null === $class) {
            return;
        }

        // Building a graph of internal dependencies in class.
        $graph = new GraphDeduplicated();

        foreach ($node->stmts as $stmt) {
            if (!($stmt instanceof Stmt\ClassMethod)) {
                continue;
            }

            $graph->insertFromName($stmt->name . '()');
            $from = $graph->get($stmt->name . '()');

            \iterate_over_node($stmt, function ($node) use ($from, $graph) {
                $nodeChecker = new NodeChecker($node);

                if ($nodeChecker->isCalledByProperty()) {
                    // use of attribute $this->xxx;
                    $name = \getNameOfNode($node);
                    $graph->addEdge($from, $graph->insertFromName($name)->get($name));
                    return;
                }

                if ($nodeChecker->isCalledByMethodCall()) {
                    // use of method call $this->xxx();
                    $name = \getNameOfNode($node->name) . '()';
                    $graph->addEdge($from, $graph->insertFromName($name)->get($name));
                    return;
                }
            });
        }

        // We count paths.
        $paths = 0;
        foreach ($graph->all() as $node) {
            $paths += $this->traverse($node);
        }

        $class->set('lcom', $paths);
    }

    /**
     * Traverse node, and return 1 if node has not been visited yet.
     *
     * @param TreeNode $node
     * @return int
     */
    private function traverse(TreeNode $node)
    {
        if ($node->visited) {
            return 0;
        }
        $node->visited = true;

        \array_map([$this, 'traverse'], $node->getAdjacents());
        return 1;
    }
}
