<?php
declare(strict_types=1);

namespace Tests\Hal\Component\Tree\Operator;

use Generator;
use Hal\Component\Tree\Graph;
use Hal\Component\Tree\Node;
use Hal\Component\Tree\Operator\CycleDetector;
use PHPUnit\Framework\TestCase;

final class CycleDetectorTest extends TestCase
{
    /**
     * Provides cyclic and acyclic graphs, with their statuses.
     *
     * @return Generator<string, array{0: Graph, 1: bool}>
     */
    public function provideGraphs(): Generator
    {
        $graph = new Graph();
        $nodeA = new Node('A');
        $nodeB = new Node('B');
        $graph->insert($nodeA);
        $graph->insert($nodeB);
        $graph->addEdge($nodeA, $nodeB);
        $graph->addEdge($nodeB, $nodeA);
        yield 'A➔B➔A' => [$graph, true];

        $graph = new Graph();
        $nodeA = new Node('A');
        $nodeB = new Node('B');
        $nodeC = new Node('C');
        $graph->insert($nodeA);
        $graph->insert($nodeB);
        $graph->insert($nodeC);
        $graph->addEdge($nodeA, $nodeB);
        $graph->addEdge($nodeB, $nodeC);
        $graph->addEdge($nodeC, $nodeA);
        yield 'A➔B➔C➔A' => [$graph, true];

        $graph = new Graph();
        $nodeA = new Node('A');
        $nodeB = new Node('B');
        $nodeC = new Node('C');
        $graph->insert($nodeA);
        $graph->insert($nodeB);
        $graph->insert($nodeC);
        $graph->addEdge($nodeA, $nodeB);
        $graph->addEdge($nodeB, $nodeC);
        $graph->addEdge($nodeC, $nodeB);
        yield 'A➔B➔C➔B' => [$graph, true];

        $graph = new Graph();
        yield 'No node' => [$graph, false];

        $graph = new Graph();
        $nodeA = new Node('A');
        $graph->insert($nodeA);
        $graph->addEdge($nodeA, $nodeA);
        yield 'A➔A' => [$graph, false];

        $graph = new Graph();
        $nodeA = new Node('A');
        $nodeB = new Node('B');
        $graph->insert($nodeA);
        $graph->insert($nodeB);
        $graph->addEdge($nodeA, $nodeB);
        yield 'A➔B' => [$graph, false];

        $graph = new Graph();
        $nodeA = new Node('A');
        $nodeB = new Node('B');
        $nodeC = new Node('C');
        $graph->insert($nodeA);
        $graph->insert($nodeB);
        $graph->insert($nodeC);
        $graph->addEdge($nodeA, $nodeB);
        $graph->addEdge($nodeC, $nodeC);
        yield 'A➔B|C➔C' => [$graph, false];

        $graph = new Graph();
        $nodeA = new Node('A');
        $nodeB = new Node('B');
        $nodeC = new Node('C');
        $graph->insert($nodeA);
        $graph->insert($nodeB);
        $graph->insert($nodeC);
        $graph->addEdge($nodeA, $nodeB);
        $graph->addEdge($nodeB, $nodeC);
        yield 'A➔B➔C' => [$graph, false];

        $graph = new Graph();
        $nodeA = new Node('A');
        $nodeB = new Node('B');
        $nodeC = new Node('C');
        $nodeD = new Node('D');
        $nodeE = new Node('E');
        $nodeF = new Node('F');
        $nodeG = new Node('G');
        $graph->insert($nodeA);
        $graph->insert($nodeB);
        $graph->insert($nodeC);
        $graph->insert($nodeD);
        $graph->insert($nodeE);
        $graph->insert($nodeF);
        $graph->insert($nodeG);
        $graph->addEdge($nodeA, $nodeB);
        $graph->addEdge($nodeB, $nodeC);
        $graph->addEdge($nodeA, $nodeD);
        $graph->addEdge($nodeD, $nodeB);
        $graph->addEdge($nodeE, $nodeC);
        $graph->addEdge($nodeF, $nodeC);
        $graph->addEdge($nodeG, $nodeG);
        yield 'A➔B➔C|A➔D➔B➔C|E➔C|F➔C|G➔G' => [$graph, false];
    }

    /**
     * @dataProvider provideGraphs
     * @param Graph $graph
     * @param bool $isCyclic
     * @return void
     */
    //#[DataProvider('provideGraphs')] //TODO: PHPUnit 10.
    public function testICanDetectCyclicTrees(Graph $graph, bool $isCyclic): void
    {
        self::assertSame($isCyclic, (new CycleDetector())->isCyclic($graph));
    }
}
