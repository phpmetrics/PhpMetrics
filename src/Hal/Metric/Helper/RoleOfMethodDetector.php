<?php
declare(strict_types=1);

namespace Hal\Metric\Helper;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use function array_pop;
use function array_reverse;
use function in_array;

/**
 * This class provides methods to analyse the role of a method.
 * Methods can be setters, getters, or something else.
 *
 * To see some examples, look at tests/Metric/Helper/RoleOfMethodDetectorTest.php
 */
final class RoleOfMethodDetector implements DetectorInterface
{
    /** @var array<string, array<array<class-string<Node>>>>  */
    private array $fingerprints = [
        'getter' => [
            [
                Stmt\ClassMethod::class,
                Stmt\Return_::class,
                Expr\PropertyFetch::class,
                Expr\Variable::class,
            ],
            [
                Stmt\ClassMethod::class,
                Stmt\Return_::class,
                Expr\PropertyFetch::class,
                Expr\Variable::class,
                Node\Name::class,
            ],
            [
                Stmt\ClassMethod::class,
                Stmt\Return_::class,
                Expr\PropertyFetch::class,
                Expr\Variable::class,
            ],
        ],
        'setter' => [
            [
                Stmt\ClassMethod::class,
                Expr\Assign::class,
                Expr\Variable::class,
                Expr\PropertyFetch::class,
                Expr\Variable::class,
                Node\Param::class,
            ],
            [
                Stmt\ClassMethod::class,
                Expr\Assign::class,
                Expr\Variable::class,
                Expr\PropertyFetch::class,
                Expr\Variable::class,
                Node\Param::class,
                Node\Name::class,
            ],
            [
                Stmt\ClassMethod::class,
                Stmt\Expression::class,
                Expr\Assign::class,
                Expr\Variable::class,
                Expr\PropertyFetch::class,
                Expr\Variable::class,
                Node\Param::class,
                Expr\Variable::class,
            ],
            [
                Stmt\ClassMethod::class,
                Stmt\Expression::class,
                Expr\Assign::class,
                Expr\Variable::class,
                Expr\PropertyFetch::class,
                Expr\Variable::class,
                Node\Param::class,
                Expr\Variable::class,
                Node\Name::class,
            ],
        ]
    ];

    /**
     * @param NodeIteratorInterface $nodeIterator
     */
    public function __construct(private readonly NodeIteratorInterface $nodeIterator)
    {
    }

    /**
     * Detects the role of a method (setter, getter, else).
     *
     * @param Node $node
     * @return string|null
     */
    public function detects(Node $node): null|string
    {
        if (! $node instanceof Stmt\ClassMethod) {
            return null;
        }

        // Build a fingerprint of the given method
        $fingerprintOfMethod = [];
        $this->nodeIterator->iterateOver($node, static function (Node $node) use (&$fingerprintOfMethod): void {
            // Ignore identifier, cast, type hint, nullable type, and PHP Attributes.
            if (
                $node instanceof Node\Identifier
                || $node instanceof Expr\Cast
                || $node instanceof Node\Name
                || $node instanceof Node\ComplexType
                || $node instanceof Node\AttributeGroup
                || $node instanceof Node\Attribute
            ) {
                return;
            }

            // Ignore fluent interface
            if ($node instanceof Stmt\Return_ && $node->expr instanceof Expr\Variable && 'this' === $node->expr->name) {
                // Remove last element that was the "this" variable, because of "return $this;".
                array_pop($fingerprintOfMethod);
                return;
            }

            $fingerprintOfMethod[] = $node::class;
        });
        // As the iteration is recursive, first elements in fingerprint are last discovered. In order to understand the
        // fingerprint as human-readable code, reverse it.
        $fingerprintOfMethod = array_reverse($fingerprintOfMethod);

        // Compare with database of fingerprints
        foreach ($this->fingerprints as $type => $fingerprints) {
            if (in_array($fingerprintOfMethod, $fingerprints, true)) {
                return $type;
            }
        }

        return null;
    }
}
