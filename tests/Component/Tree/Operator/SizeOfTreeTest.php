<?php
declare(strict_types=1);

namespace Tests\Hal\Component\Tree\Operator;

use Generator;
use Hal\Component\Tree\Graph;
use Hal\Component\Tree\Node;
use Hal\Component\Tree\Operator\SizeOfTree;
use Hal\Exception\GraphException\NoSizeForCyclicGraphException;
use PHPUnit\Framework\TestCase;

final class SizeOfTreeTest extends TestCase
{
    /**
     * Provides cyclic graphs with several examples of cyclic relations.
     *
     * @return Generator<string, array{0: Graph}>
     */
    public function provideCyclicGraphs(): Generator
    {
        $graph = new Graph();
        $nodeA = new Node('A');
        $nodeB = new Node('B');
        $graph->insert($nodeA);
        $graph->insert($nodeB);
        $graph->addEdge($nodeA, $nodeB);
        $graph->addEdge($nodeB, $nodeA);
        yield 'A➔B➔A' => [$graph];

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
        yield 'A➔B➔C➔A' => [$graph];

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
        yield 'A➔B➔C➔B' => [$graph];
    }

    /**
     * @dataProvider provideCyclicGraphs
     * @param Graph $graph
     * @return void
     */
    //#[DataProvider('provideCyclicGraphs')] //TODO: PHPUnit 10.
    public function testICantHaveSizeOfCyclicTree(Graph $graph): void
    {
        $this->expectExceptionObject(NoSizeForCyclicGraphException::incalculableSize());
        new SizeOfTree($graph);
    }

    /**
     * Provides acyclic graphs with their average height.
     *
     * @return Generator<string, array{0: Graph, 1: float}>
     */
    public function provideAcyclicGraphs(): Generator
    {
        $graph = new Graph();
        yield 'No node' => [$graph, 0];

        $graph = new Graph();
        $nodeA = new Node('A');
        $graph->insert($nodeA);
        $graph->addEdge($nodeA, $nodeA);
        yield 'A➔A' => [$graph, 1];

        $graph = new Graph();
        $nodeA = new Node('A');
        $nodeB = new Node('B');
        $graph->insert($nodeA);
        $graph->insert($nodeB);
        $graph->addEdge($nodeA, $nodeB);
        yield 'A➔B' => [$graph, 2];

        $graph = new Graph();
        $nodeA = new Node('A');
        $nodeB = new Node('B');
        $nodeC = new Node('C');
        $graph->insert($nodeA);
        $graph->insert($nodeB);
        $graph->insert($nodeC);
        $graph->addEdge($nodeA, $nodeB);
        $graph->addEdge($nodeC, $nodeC);
        yield 'A➔B|C➔C' => [$graph, 1.5];

        $graph = new Graph();
        $nodeA = new Node('A');
        $nodeB = new Node('B');
        $nodeC = new Node('C');
        $graph->insert($nodeA);
        $graph->insert($nodeB);
        $graph->insert($nodeC);
        $graph->addEdge($nodeA, $nodeB);
        $graph->addEdge($nodeB, $nodeC);
        yield 'A➔B➔C' => [$graph, 3];

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
        // (A➔B➔C) is ignored as shorter than (A➔D➔B➔C), wth same root node.
        // Therefore, expected average is (4+2+2+1)/4 = 2.25
        yield 'A➔B➔C|A➔D➔B➔C|E➔C|F➔C|G➔G' => [$graph, 2.25];
    }

    /**
     * @dataProvider provideAcyclicGraphs
     * @param Graph $graph
     * @param float $expectedValue
     * @return void
     */
    //#[DataProvider('provideAcyclicGraphs')] //TODO: PHPUnit 10.
    public function testICanCalculateAverageSizeOfTree(Graph $graph, float $expectedValue): void
    {
        self::assertSame($expectedValue, (new SizeOfTree($graph))->getAverageHeightOfGraph());
    }
}
