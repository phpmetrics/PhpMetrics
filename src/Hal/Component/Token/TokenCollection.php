<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Token;
use Traversable;

/**
 * Representation of Collection of oken
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class TokenCollection implements \ArrayAccess, \IteratorAggregate, \Countable {

    /**
     * @var array
     */
    private $tokens = array();

    /**
     * Constructor
     *
     * @param $tokens
     */
    public function __construct(array $tokens)
    {
        foreach($tokens as &$token) {
            if(!$token instanceof Token) {
                $token = new Token($token);
            }
        }
        $this->tokens = $tokens;
    }

    /**
     * Push token
     *
     * @param Token $token
     * @return $this
     */
    public function push(Token $token) {
        array_push($this->tokens, $token);
        return $this;
    }

    /**
     * As string representation
     *
     * @return string
     */
    public function asString() {
        $c = '';
        foreach($this->tokens as $token) {
            $c .= $token->asString();
        }
        $c = preg_replace('!(\n\s+)!', PHP_EOL, $c);
        return trim($c);
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
       return isset($this->tokens[$offset]);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        return $this->tokens[$offset];
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        if(!$value instanceof Token) {
            $value = new Token($value);
        }
        $this->tokens[$offset] = $value;
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        unset($this->tokens[$offset]);
    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->tokens);
    }

    /**
     * @inheritdoc
     */
    public function count() {
        return sizeof($this->tokens, COUNT_NORMAL);
    }

    /**
     * As array representation
     *
     * @return array
     */
    public function asArray() {
        return $this->tokens;
    }
}