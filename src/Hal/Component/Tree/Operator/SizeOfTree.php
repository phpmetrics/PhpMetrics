<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Tree\Operator;

use Hal\Component\Tree\Graph;
use Hal\Component\Tree\Node;

class SizeOfTree
{
    /**
     * @var Graph
     */
    private $graph;

    /**
     * SizeOfTree constructor.
     * @param Graph $graph
     */
    public function __construct(Graph $graph)
    {
        if ((new CycleDetector())->isCyclic($graph)) {
            throw new \LogicException('Cannot get size informations of cyclic graph');
        }

        $this->graph = $graph;
    }

    /**
     * Get depth of node
     *
     * @param Node $node
     * @return int
     */
    public function getDepthOfNode(Node $node)
    {

        $edges = $node->getEdges();

        if (0 === sizeof($edges)) {
            return 0;
        }

        // our tree is not binary : interface can have more than one parent
        $max = 0;
        foreach ($edges as $edge) {

            if ($edge->getFrom() == $node) {
                continue;
            }

            $n = 1 + $this->getDepthOfNode($edge->getFrom());
            if ($n > $max) {
                $max = $n;
            }
        }

        return $max;
    }

    /**
     * Get depth of node
     *
     * @param Node $node
     * @return int
     */
    public function getNumberOfChilds(Node $node, $uniqs = false)
    {

        $edges = $node->getEdges();

        if (0 === sizeof($edges)) {
            return 0;
        }

        // our tree is not binary : interface can have more than one parent
        $max = 0;
        $n = 0;

        foreach ($edges as $edge) {


            if ($edge->getTo() == $node) {
                continue;
            }

            if (true == $uniqs && $edge->getTo()->visited) {
                continue;
            }
            $edge->getTo()->visited = true;

            $n += 1 + $this->getNumberOfChilds($edge->getTo(), $uniqs);

            $edge->getTo()->visited = false;


            if ($n > $max) {
                $max = $n;
            }
        }

        return $max;
    }

    /**
     * Get average height of graph
     *
     * @return float
     */
    public function getAverageHeightOfGraph()
    {
        $ns = [];
        foreach ($this->graph->getRootNodes() as $node) {
            array_push($ns, $this->getLongestBranch($node));
        }
        return round(array_sum($ns) / max(1, sizeof($ns)), 2);
    }

    /**
     * @param Node $node
     * @return int
     */
    public function getLongestBranch(Node $node)
    {
        $max = 1;
        foreach ($node->getEdges() as $edge) {

            if ($node == $edge->getTo()) {
                // only descendants
                continue;
            }

            $n = 1 + $this->getLongestBranch($edge->getTo());

            if ($n > $max) {
                $max = $n;
            }
        }

        return $max;
    }
}
