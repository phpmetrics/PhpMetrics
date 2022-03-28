<?php
declare(strict_types=1);

namespace Tests\Hal\Component\Tree;

use Hal\Component\Tree\Graph;
use Hal\Component\Tree\Node;
use Hal\Exception\GraphException\NodeAlreadyDefinedException;
use Hal\Exception\GraphException\OriginNodeMissingException;
use Hal\Exception\GraphException\TargetNodeMissingException;
use PHPUnit\Framework\TestCase;

final class GraphTest extends TestCase
{
    public function testICanInsertNodes(): void
    {
        $nodeA = new Node('A');
        $nodeB = new Node('B');

        $graph = new Graph();
        $graph->insert($nodeA);
        $graph->insert($nodeB);

        self::assertSame(['A' => $nodeA, 'B' => $nodeB], $graph->all());
    }

    public function testICanCheckNodes(): void
    {
        $nodeA = new Node('A');

        $graph = new Graph();
        $graph->insert($nodeA);

        self::assertTrue($graph->has('A'));
        self::assertFalse($graph->has('B'));
    }

    public function testICanGetNodes(): void
    {
        $nodeA = new Node('A');

        $graph = new Graph();
        $graph->insert($nodeA);

        self::assertSame($nodeA, $graph->get('A'));
        self::assertNull($graph->get('B'));
    }

    public function testICantInsertSameNodeTwice(): void
    {
        $nodeA = new Node('A');

        $graph = new Graph();
        $graph->insert($nodeA);

        $this->expectExceptionObject(NodeAlreadyDefinedException::inGraph($nodeA));

        $graph->insert($nodeA);
    }

    public function testICanAddEdges(): void
    {
        $nodeA = new Node('A');
        $nodeB = new Node('B');

        $graph = new Graph();
        $graph->insert($nodeA);
        $graph->insert($nodeB);
        $graph->addEdge($nodeA, $nodeB);

        [$edgeAB] = $graph->getEdges();
        self::assertSame($nodeA, $edgeAB->getFrom());
        self::assertSame($nodeB, $edgeAB->getTo());
    }

    public function testICantAddEdgesIfOriginNodeIsMissing(): void
    {
        $nodeA = new Node('A');
        $nodeB = new Node('B');

        $graph = new Graph();
        $graph->insert($nodeB);

        $this->expectExceptionObject(OriginNodeMissingException::inGraph($nodeA));

        $graph->addEdge($nodeA, $nodeB);
    }

    public function testICantAddEdgesIfTargetNodeIsMissing(): void
    {
        $nodeA = new Node('A');
        $nodeB = new Node('B');

        $graph = new Graph();
        $graph->insert($nodeA);

        $this->expectExceptionObject(TargetNodeMissingException::inGraph($nodeB));

        $graph->addEdge($nodeA, $nodeB);
    }

    public function testICanResetVisitsForNodes(): void
    {
        $nodeA = new Node('A');
        $nodeB = new Node('B');

        $graph = new Graph();
        $graph->insert($nodeA);
        $graph->insert($nodeB);

        $nodeA->visited = true;
        $nodeB->visited = true;

        $graph->resetVisits();

        self::assertFalse($nodeA->visited);
        self::assertFalse($nodeB->visited);
    }

    /**
     * For this test, we will play with this following graph:
     *
     *  A --> B --> C
     *  D -->/     /
     *  E ------>/
     *
     * So that [A, D, E] are the root nodes.
     *
     *  Then, we will add node F, like
     *
     *  F --> A ...
     *   \--> D ...
     *   \--> E ...
     *
     * So that [F] will become the root node.
     *
     * @return void
     */
    public function testICanListAllRootNodesFromGraph(): void
    {
        $graph = new Graph();
        $nodeA = new Node('A');
        $nodeB = new Node('B');
        $nodeC = new Node('C');
        $nodeD = new Node('D');
        $nodeE = new Node('E');

        $graph->insert($nodeA);
        $graph->insert($nodeB);
        $graph->insert($nodeC);
        $graph->insert($nodeD);
        $graph->insert($nodeE);

        $graph->addEdge($nodeA, $nodeB);
        $graph->addEdge($nodeB, $nodeC);
        $graph->addEdge($nodeD, $nodeB);
        $graph->addEdge($nodeE, $nodeC);

        self::assertSame([$nodeA, $nodeD, $nodeE], $graph->getRootNodes());

        $nodeF = new Node('F');
        $graph->insert($nodeF);
        $graph->addEdge($nodeF, $nodeA);
        $graph->addEdge($nodeF, $nodeD);
        $graph->addEdge($nodeF, $nodeE);

        self::assertSame([$nodeF], $graph->getRootNodes());
    }

    /**
     * Expected graph to be displayed:
     *
     *  F --> A --> B --> C
     *   \--> D -->/     /
     *   \--> E ------>/
     *
     * @return void
     */
    public function testICanDisplayGraph(): void
    {
        $graph = new Graph();
        $nodeA = new Node('A');
        $nodeB = new Node('B');
        $nodeC = new Node('C');
        $nodeD = new Node('D');
        $nodeE = new Node('E');
        $nodeF = new Node('F');

        $graph->insert($nodeA);
        $graph->insert($nodeB);
        $graph->insert($nodeC);
        $graph->insert($nodeD);
        $graph->insert($nodeE);
        $graph->insert($nodeF);

        $graph->addEdge($nodeA, $nodeB);
        $graph->addEdge($nodeB, $nodeC);
        $graph->addEdge($nodeD, $nodeB);
        $graph->addEdge($nodeE, $nodeC);
        $graph->addEdge($nodeF, $nodeA);
        $graph->addEdge($nodeF, $nodeD);
        $graph->addEdge($nodeF, $nodeE);

        $expectedOutput = "A;\nB;\nC;\nD;\nE;\nF;\nA ➔ B;\nB ➔ C;\nD ➔ B;\nE ➔ C;\nF ➔ A;\nF ➔ D;\nF ➔ E;\n";
        self::assertSame($expectedOutput, $graph->__toString());
    }

    public function testICanHaveTwiceTheSameEdge(): void
    {
        $graph = new Graph();
        $nodeA = new Node('A');
        $nodeB = new Node('B');
        $graph->insert($nodeA);
        $graph->insert($nodeB);

        $graph->addEdge($nodeA, $nodeB);
        $graph->addEdge($nodeA, $nodeB);

        $expectedOutput = "A;\nB;\nA ➔ B;\nA ➔ B;\n";
        self::assertSame($expectedOutput, $graph->__toString());
    }
}
