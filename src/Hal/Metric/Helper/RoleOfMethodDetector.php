<?php
namespace Hal\Metric\Helper;

use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Param;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Assign;

/**
 * Class RoleOfMethodDetector
 * @package Hal\Metric\Helper
 */
class RoleOfMethodDetector
{

    /**
     * @var array
     */
    private $fingerprints = [
        'getter' => [
            ClassMethod::class,
            Return_::class,
            PropertyFetch::class,
            Variable::class,
        ],
        'setter' => [
            ClassMethod::class,
            Assign::class,
            Variable::class,
            PropertyFetch::class,
            Variable::class,
            Param::class,
        ]
    ];

    /**
     * @param $node
     * @return string|null
     */
    public function detects($node)
    {

        if (!$node instanceof ClassMethod) {
            return null;
        }

        // build a fingerprint of the given method
        $fingerprintOfMethod = [];
        \iterate_over_node($node, function ($node) use (&$fingerprintOfMethod) {

            // avoid cast
            if ($node instanceof Cast) {
                return;
            }

            // avoid fluent interface
            if ($node instanceof Return_ && $node->expr instanceof Variable && 'this' === $node->expr->name) {
                unset($fingerprintOfMethod[\count($fingerprintOfMethod) - 1]);
                return;
            }

            $fingerprintOfMethod[] = \get_class($node);
        });
        $fingerprintOfMethod = \array_reverse($fingerprintOfMethod);

        // compare with database of fingerprints
        foreach ($this->fingerprints as $type => $fingerprint) {
            if ($fingerprint == $fingerprintOfMethod) {
                return $type;
            }
        }

        return null;
    }
}
