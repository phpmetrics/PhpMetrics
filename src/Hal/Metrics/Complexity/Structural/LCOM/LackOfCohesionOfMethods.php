<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Metrics\Complexity\Structural\LCOM;
use Hal\Component\Reflected\Klass;
use Hal\Component\Reflected\Method;
use Hal\Component\Tree\Graph;
use Hal\Component\Tree\Node;
use Hal\Metrics\ClassMetric;

/**
 * Calculates lack of cohesion method
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class LackOfCohesionOfMethods implements ClassMetric {


    /**
     * @param Klass $class
     * @return Result
     */
    public function calculate(Klass $class) {

        $graph = new Graph();

        // attributes in graph are prefixed with '_attr_' string
        foreach($class->getMethods() as $method) {

            // avoid getters and setters
            if($method->isGetter() ||$method->isSetter()) {
                continue;
            }

            if(null === ($from = $graph->get($method->getName()))) {
                $from = new Node($method->getName());
                $graph->insert($from);
            }

            // calls
            foreach($method->getCalls() as $call) {

                if(!$call->isItself()) {
                    continue;

                }

                if(null === ($to = $graph->get($call->getMethodName()))) {
                    $to = new Node($call->getMethodName());
                    $graph->insert($to);
                }
                $graph->addEdge($from, $to);
            }

            // attributes
            foreach($method->getTokens() as $token) {
                if(preg_match('!\$this\->(\w+)$!', $token, $matches)) {
                    list(, $attribute) = $matches;

                    if(null === ($to = $graph->get('_attr_' . $attribute))) {
                        $to = new Node('_attr_' . $attribute);
                        $graph->insert($to);
                    }
                    $graph->addEdge($from, $to);
                }
            }

        }

        // iterate over nodes, and count paths
        $paths = 0;
        foreach($graph->all() as $node) {
            $paths += $this->traverse($node);
        }

        $result = new Result;
        $result->setLcom($paths);
        return $result;

    }


    /**
     * Traverse node, and return 1 if node has not been visited yet
     *
     * @param Node $node
     * @return int
     */
    private function traverse(Node $node)
    {
        if($node->visited) {
            return 0;
        }
        $node->visited = true;

        foreach($node->getAdjacents() as $adjacent) {
            $this->traverse($adjacent);
        }

        return 1;
    }
}





