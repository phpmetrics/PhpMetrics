<?php

namespace Test;

use Hal\Component\Tree\Graph;
use Hal\Component\Tree\Node;
use Hal\Component\Tree\Operator\CycleDetector;

/**
 * @group tree
 */
class CycleDetectorTest extends \PHPUnit\Framework\TestCase
{
    public function testCycleIsDetected(): void
    {
        $graph = new Graph();
        $a = new Node('A');
        $b = new Node('B');
        $c = new Node('C');
        $d = new Node('D');
        $e = new Node('E');
        $f = new Node('F');

        $graph->insert($a)->insert($b)->insert($c)->insert($d)->insert($e)->insert($f);

        // cyclic
        $graph->addEdge($a, $b); // A -> B
        $graph->addEdge($b, $c); // B -> C
        $graph->addEdge($c, $d); // C -> D
        $graph->addEdge($d, $a); // D -> A

        // not cyclic
        $graph->addEdge($e, $a); // E -> A
        $graph->addEdge($f, $a); // F -> A

        $cycleDetector = new CycleDetector();
        $isCyclic = $cycleDetector->isCyclic($graph);

        $this->assertTrue($isCyclic);
        $this->assertTrue($a->cyclic);
        $this->assertTrue($b->cyclic);
        $this->assertTrue($c->cyclic);
        $this->assertTrue($d->cyclic);
        $this->assertFalse($e->cyclic);
        $this->assertFalse($f->cyclic);
    }

    public function testAllCyclesAreFound(): void
    {
        $graph = new Graph();
        $a = new Node('A');
        $b = new Node('B');
        $c = new Node('C');
        $d = new Node('D');
        $e = new Node('E');
        $f = new Node('F');

        $graph->insert($a)->insert($b)->insert($c)->insert($d)->insert($e)->insert($f);

        // cyclic
        $graph->addEdge($a, $b); // A -> B
        $graph->addEdge($b, $c); // B -> C
        $graph->addEdge($c, $d); // C -> D
        $graph->addEdge($d, $a); // D -> A

        // cyclic
        $graph->addEdge($e, $f); // E -> F
        $graph->addEdge($f, $e); // F -> E

        $cycleDetector = new CycleDetector();
        $isCyclic = $cycleDetector->isCyclic($graph);

        $this->assertTrue($isCyclic);
        $this->assertTrue($a->cyclic);
        $this->assertTrue($b->cyclic);
        $this->assertTrue($c->cyclic);
        $this->assertTrue($d->cyclic);
        $this->assertTrue($e->cyclic);
        $this->assertTrue($f->cyclic);
    }

    public function testCycleIsNotDetected(): void
    {
        $graph = new Graph();
        $a = new Node('A');
        $b = new Node('B');
        $c = new Node('C');
        $d = new Node('D');
        $e = new Node('E');
        $f = new Node('F');

        $graph->insert($a)->insert($b)->insert($c)->insert($d)->insert($e)->insert($f);

        // not cyclic
        $graph->addEdge($a, $b); // A -> B
        $graph->addEdge($b, $c); // B -> C
        $graph->addEdge($c, $d); // C -> D

        // not cyclic
        $graph->addEdge($d, $e); // D -> E
        $graph->addEdge($e, $f); // E -> F

        $cycleDetector = new CycleDetector();
        $isCyclic = $cycleDetector->isCyclic($graph);

        $this->assertFalse($isCyclic);
        $this->assertFalse($a->cyclic);
        $this->assertFalse($b->cyclic);
        $this->assertFalse($c->cyclic);
        $this->assertFalse($d->cyclic);
        $this->assertFalse($e->cyclic);
        $this->assertFalse($f->cyclic);
    }

    public function testPartCycleIsDetected(): void
    {
        $graph = new Graph();
        $a = new Node('A');
        $b = new Node('B');
        $c = new Node('C');
        $d = new Node('D');
        $e = new Node('E');

        $graph->insert($a)->insert($b)->insert($c)->insert($d)->insert($e);

        // cyclic
        $graph->addEdge($a, $b); // A -> B
        $graph->addEdge($b, $c); // B -> C
        $graph->addEdge($c, $a); // C -> A

        // not cyclic
        $graph->addEdge($d, $e); // D -> E
        $graph->addEdge($e, $a); // E -> A

        $cycleDetector = new CycleDetector();
        $isCyclic = $cycleDetector->isCyclic($graph);

        $this->assertTrue($isCyclic);
        $this->assertTrue($a->cyclic);
        $this->assertTrue($b->cyclic);
        $this->assertTrue($c->cyclic);
        $this->assertFalse($d->cyclic);
        $this->assertTrue($e->cyclic);
    }
}
