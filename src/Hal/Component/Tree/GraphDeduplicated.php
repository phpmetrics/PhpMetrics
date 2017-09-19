<?php

namespace Hal\Component\Tree;

class GraphDeduplicated extends Graph
{
    /**
     * @var array list of already present edges in this graph
     */
    private $edgesMap = [];

    /**
     * @param Node $from
     * @param Node $to
     *
     * @return $this
     */
    public function addEdge(Node $from, Node $to)
    {
        $key = $from->getUniqueId().'->'.$to->getUniqueId();

        if (isset($this->edgesMap[$key])) {
            return $this;
        }

        $this->edgesMap[$key] = true;

        return parent::addEdge($from, $to);
    }
}
