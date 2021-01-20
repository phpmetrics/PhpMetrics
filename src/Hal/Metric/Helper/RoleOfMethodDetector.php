<?php
namespace Hal\Metric\Helper;

use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;

/**
 * @package Hal\Metric\Helper
 */
class RoleOfMethodDetector
{
    /**
     * @var array
     */
    private $fingerprints = [
        'getter' => [
            [
                'PhpParser\\Node\\Stmt\\ClassMethod',
                'PhpParser\\Node\\Stmt\\Return_',
                'PhpParser\\Node\\Expr\\PropertyFetch',
                'PhpParser\\Node\\Expr\\Variable',
            ],
            [
                'PhpParser\\Node\\Stmt\\ClassMethod',
                'PhpParser\\Node\\Stmt\\Return_',
                'PhpParser\\Node\\Expr\\PropertyFetch',
                'PhpParser\\Node\\Expr\\Variable',
                'PhpParser\\Node\\Name',
            ],
            [
                'PhpParser\\Node\\Stmt\\ClassMethod',
                'PhpParser\\Node\\Stmt\\Return_',
                'PhpParser\\Node\\Expr\\PropertyFetch',
                'PhpParser\\Node\\Expr\\Variable',
            ],
        ],
        'setter' => [
            [
                'PhpParser\\Node\\Stmt\\ClassMethod',
                'PhpParser\\Node\\Expr\\Assign',
                'PhpParser\\Node\\Expr\\Variable',
                'PhpParser\\Node\\Expr\\PropertyFetch',
                'PhpParser\\Node\\Expr\\Variable',
                'PhpParser\\Node\\Param',
            ],
            [
                'PhpParser\\Node\\Stmt\\ClassMethod',
                'PhpParser\\Node\\Expr\\Assign',
                'PhpParser\\Node\\Expr\\Variable',
                'PhpParser\\Node\\Expr\\PropertyFetch',
                'PhpParser\\Node\\Expr\\Variable',
                'PhpParser\\Node\\Param',
                'PhpParser\\Node\\Name',
            ],
            // nicik/php-parser:^4
            [
                'PhpParser\\Node\\Stmt\\ClassMethod',
                'PhpParser\\Node\\Stmt\\Expression',
                'PhpParser\\Node\\Expr\\Assign',
                'PhpParser\\Node\\Expr\\Variable',
                'PhpParser\\Node\\Expr\\PropertyFetch',
                'PhpParser\\Node\\Expr\\Variable',
                'PhpParser\\Node\\Param',
                'PhpParser\\Node\\Expr\\Variable',
            ],
            [
                'PhpParser\\Node\\Stmt\\ClassMethod',
                'PhpParser\\Node\\Stmt\\Expression',
                'PhpParser\\Node\\Expr\\Assign',
                'PhpParser\\Node\\Expr\\Variable',
                'PhpParser\\Node\\Expr\\PropertyFetch',
                'PhpParser\\Node\\Expr\\Variable',
                'PhpParser\\Node\\Param',
                'PhpParser\\Node\\Expr\\Variable',
                'PhpParser\\Node\\Name',
            ],[
                // function setOk(?bool $ok): self { $this->isOk = $ok; return $this; }
                'PhpParser\\Node\\Stmt\\ClassMethod',
                'PhpParser\\Node\\Stmt\\Expression',
                'PhpParser\\Node\\Expr\\Assign',
                'PhpParser\\Node\\Expr\\Variable',
                'PhpParser\\Node\\Expr\\PropertyFetch',
                'PhpParser\\Node\\Expr\\Variable',
                'PhpParser\\Node\\Param',
                'PhpParser\\Node\\Expr\\Variable',
            ],
        ]
    ];

    /**
     * @param $node
     * @return string|null
     */
    public function detects($node)
    {
        if (! $node instanceof ClassMethod) {
            return null;
        }

        // build a fingerprint of the given method
        $fingerprintOfMethod = [];
        iterate_over_node($node, function ($node) use (&$fingerprintOfMethod) {
            // avoid identifier (php-parser:^4)
            if ($node instanceof Identifier) {
                return;
            }

            // avoid cast
            if ($node instanceof Cast) {
                return;
            }

            // avoid fluent interface
            if ($node instanceof Return_ && $node->expr instanceof Variable && $node->expr->name === 'this') {
                unset($fingerprintOfMethod[count($fingerprintOfMethod) - 1]);
                return;
            }

            // avoid type hint
            if ($node instanceof Name) {
                return;
            }

            // avoid nullable type
            if ($node instanceof NullableType) {
                return;
            }

            $fingerprintOfMethod[] = get_class($node);
        });
        $fingerprintOfMethod = array_reverse($fingerprintOfMethod);

        // compare with database of fingerprints
        foreach ($this->fingerprints as $type => $fingerprints) {
            if (in_array($fingerprintOfMethod, $fingerprints, true)) {
                return $type;
            }
        }

        return null;
    }
}
