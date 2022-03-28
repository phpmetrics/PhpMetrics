<?php
declare(strict_types=1);

namespace Tests\Hal\Component\Ast;

use Hal\Component\Ast\NodeTraverser;
use Phake;
use Phake\IMock;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\NodeTraverser as Mother;
use PhpParser\NodeVisitor;
use PHPUnit\Framework\TestCase;
use function is_array;

final class NodeTraverserTest extends TestCase
{
    public function testItCanBeTraversed(): void
    {
        // Visitors that will be applied 1 by 1.
        $mocksVisitor = [
            Phake::mock(NodeVisitor::class), // enterNode => null, leaveNode => null
            Phake::mock(NodeVisitor::class), // enterNode => DONT_TRAVERSE_CHILDREN, leaveNode => null
            Phake::mock(NodeVisitor::class), // enterNode => otherNode, leaveNode => null
            Phake::mock(NodeVisitor::class), // enterNode => null, leaveNode => REMOVE_NODE
            Phake::mock(NodeVisitor::class), // enterNode => null, leaveNode => array<Node>
            Phake::mock(NodeVisitor::class), // enterNode => null, leaveNode => otherNode
        ];
        // Input of nodes.
        $mocksNode = [
            [Phake::mock(Node::class)], // array<Node> for recursive call
            Phake::mock(Phake::class), // Node that is not a node (so, abort)
            Phake::mock(ClassLike::class), // Node that is a ClassLike node (so, don't traverse children)
        ];
        // Nodes used during some processes as a replacement of some input nodes.
        $mocksReplacementNode = [
            Phake::mock(Node::class), // Used by enterNode, when replacing.
            Phake::mock(Node::class), // Used by leaveNode with next one, when replacing by array
            Phake::mock(Node::class), // Used by leaveNode with prev one, when replacing by array
            Phake::mock(Node::class), // Used by leaveNode, when replacing.
        ];
        // Sub-node, used when traversing children (internal PhpParser behavior).
        $subNode = Phake::mock(Node::class);

        foreach ($mocksNode as $mockNode) {
            if (is_array($mockNode)) {
                [$mockNode] = $mockNode;
            }
            if (!($mockNode instanceof Node)) {
                continue;
            }

            // Prepare mocks + set a dynamic flag to all visitors that will enter into children nodes.
            Phake::when($mocksVisitor[0])->__call('enterNode', [$mockNode])->thenReturn(null);
            Phake::when($mocksVisitor[0])->__call('leaveNode', [$mockNode])->thenReturn(null);
            $mocksVisitor[0]->doesTraverseChildren = true;
            Phake::when($mocksVisitor[1])->__call('enterNode', [$mockNode])->thenReturn(Mother::DONT_TRAVERSE_CHILDREN);
            Phake::when($mocksVisitor[1])->__call('leaveNode', [$mockNode])->thenReturn(null);
            $mocksVisitor[1]->doesTraverseChildren = false;
            Phake::when($mocksVisitor[2])->__call('enterNode', [$mockNode])->thenReturn($mocksReplacementNode[0]);
            Phake::when($mocksVisitor[2])->__call('leaveNode', [$mockNode])->thenReturn(null);
            $mocksVisitor[2]->doesTraverseChildren = true;
            Phake::when($mocksVisitor[3])->__call('enterNode', [$mockNode])->thenReturn(null);
            Phake::when($mocksVisitor[3])->__call('leaveNode', [$mockNode])->thenReturn(Mother::REMOVE_NODE);
            $mocksVisitor[3]->doesTraverseChildren = true;
            Phake::when($mocksVisitor[4])->__call('enterNode', [$mockNode])->thenReturn(null);
            Phake::when($mocksVisitor[4])->__call('leaveNode', [$mockNode])
                ->thenReturn([$mocksReplacementNode[1], $mocksReplacementNode[2]]);
            $mocksVisitor[4]->doesTraverseChildren = true;
            Phake::when($mocksVisitor[5])->__call('enterNode', [$mockNode])->thenReturn(null);
            Phake::when($mocksVisitor[5])->__call('leaveNode', [$mockNode])->thenReturn($mocksReplacementNode[3]);
            $mocksVisitor[5]->doesTraverseChildren = true;

            /** @var IMock&Node $mockNode */
            Phake::when($mockNode)->__call('getSubNodeNames', [])->thenReturn(['testSubNode']);
            $mockNode->{'testSubNode'} = $subNode;
            foreach ($mocksVisitor as $mockVisitor) {
                Phake::when($mockVisitor)->__call('enterNode', [$subNode])->thenReturn(Mother::STOP_TRAVERSAL);
            }
        }

        // Expected result depending on the visitors.
        $expectedNewNodes = [
            $mocksNode,
            $mocksNode,
            $mocksNode,
            [[], $mocksNode[1]],
            [[$mocksReplacementNode[1], $mocksReplacementNode[2]], $mocksNode[1], $mocksReplacementNode[1], $mocksReplacementNode[2]],
            $mocksNode
        ];

        $nodeTraverser = new NodeTraverser();

        foreach ($mocksVisitor as $index => $mockVisitor) {
            Phake::when($mockVisitor)->__call('beforeTraverse', [Phake::anyParameters()])->thenReturn(null);
            Phake::when($mockVisitor)->__call('afterTraverse', [Phake::anyParameters()])->thenReturn(null);

            $nodeTraverser->addVisitor($mockVisitor);

            $newNodes = $nodeTraverser->traverse($mocksNode);

            Phake::verify($mockVisitor)->__call('beforeTraverse', [$mocksNode]);
            Phake::verify($mockVisitor)->__call('afterTraverse', [$newNodes]);

            Phake::verify($mockVisitor)->__call('enterNode', [$mocksNode[0][0]]);
            Phake::verify($mockVisitor)->__call('leaveNode', [$mocksNode[0][0]]);
            Phake::verify($mockVisitor)->__call('enterNode', [$mocksNode[2]]);
            Phake::verify($mockVisitor)->__call('leaveNode', [$mocksNode[2]]);

            if ($mockVisitor->doesTraverseChildren) {
                Phake::verify($mocksNode[0][0], Phake::atLeast(1))->__call('getSubNodeNames', []);
                Phake::verify($mockVisitor)->__call('enterNode', [$subNode]);
            }

            self::assertSame($expectedNewNodes[$index], $newNodes);

            Phake::verifyNoOtherInteractions($mockVisitor);
            Phake::verifyNoOtherInteractions($mocksNode[0][0]);
            Phake::verifyNoOtherInteractions($mocksNode[1]);
            Phake::verifyNoOtherInteractions($mocksNode[2]);

            $nodeTraverser->removeVisitor($mockVisitor);
        }
    }
}
