<?php
/**
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Tree;

/**
 * Class GraphDeduplicated
 *
 * @package Hal\Component\Tree
 */
class GraphDeduplicated extends Graph
{
    /** @var bool[] List of already present edges in this graph. */
    private $edgesMap = [];

    /**
     * Add an edge to the map only if not already there.
     *
     * @param Node $from Node where the edge starts.
     * @param Node $to Node where the edge ends.
     * @return $this
     */
    public function addEdge(Node $from, Node $to)
    {
        $key = $from->getUniqueId() . '->' . $to->getUniqueId();

        if (isset($this->edgesMap[$key])) {
            return $this;
        }

        $this->edgesMap[$key] = true;

        return parent::addEdge($from, $to);
    }
}
