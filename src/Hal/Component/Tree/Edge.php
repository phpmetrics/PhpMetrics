<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Tree;

class Edge
{
    /**
     * @var Node
     */
    private $from;

    /**
     * @var Node
     */
    private $to;

    /**
     * @var boolean
     */
    public $cyclic = false;

    /**
     * Edge constructor.
     * @param Node $from
     * @param Node $to
     */
    public function __construct(Node $from, Node $to)
    {
        $this->from = $from;
        $this->to = $to;
    }

    /**
     * @return Node
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return Node
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @return string
     */
    public function asString()
    {
        return sprintf('%s -> %s', $this->from->getKey(), $this->to->getKey());
    }
}
