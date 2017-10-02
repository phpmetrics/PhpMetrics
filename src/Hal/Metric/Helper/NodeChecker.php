<?php
/**
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Metric\Helper;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;

/**
 * Class NodeChecker
 * @package Hal\Metric\Helper
 */
class NodeChecker
{
    /** @var Node The node to be checked. */
    protected $node;

    /**
     * NodeChecker constructor.
     *
     * @param Node $node The node to be checked.
     */
    public function __construct(Node $node)
    {
        $this->node = $node;
    }

    /**
     * Returns TRUE if the given node is a reference to a property being fetched.
     * @return bool
     */
    public function isCalledByProperty()
    {
        return (
            $this->node instanceof PropertyFetch && isset($this->node->var->name) && 'this' === $this->node->var->name
        );
    }

    /**
     * Returns TRUE if the given node is a reference to a method call.
     * @return bool
     */
    public function isCalledByMethodCall()
    {
        return (
            $this->node instanceof MethodCall
            && !($this->node->var instanceof New_)
            && isset($this->node->var->name)
            && 'this' === \getNameOfNode($this->node->var)
        );
    }
}
