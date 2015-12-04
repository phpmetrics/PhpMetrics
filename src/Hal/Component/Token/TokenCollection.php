<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Token;

/**
 * Representation of Collection of tokens
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class TokenCollection implements \ArrayAccess, \IteratorAggregate, \Countable {

    /**
     * @var array
     */
    protected $tokens = array();

    /**
     * Constructor
     *
     * @param $tokens
     */
    public function __construct(array $tokens)
    {
        foreach($tokens as $index => &$token) {
            if(!$token instanceof Token) {
                $token = new Token($token);
            }
        }
//        $this->tokens = $tokens;return;
        $compability = new TokenCollectionCompatibility();
        $this->tokens = $compability->decorate($tokens);
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
     * Extract part of tokens (equivalent of array_slice())
     *
     * @param $start
     * @param $end
     * @return TokenCollection
     */
    public function extract($start, $end) {
        $concerned = array_slice($this->asArray(), $start, $end - $start );
        return new TokenCollection($concerned);
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
        $c = preg_replace('!(\n\s+)!', "\n", $c);
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

    /**
     * Replace token with another
     *
     * @param $index
     * @param Token $token
     * @return $this
     */
    public function replace($index, Token $token) {
        $tokens = $this->tokens;
        $tokens[$index] = $token;
        return new TokenCollection($tokens);
    }

    /**
     * Remove part of tokens
     *
     * @param $index
     * @param null $end
     * @return TokenCollection
     */
    public function remove($index, $end = null)
    {
        $tokens = $this->tokens;
        if (null === $end) {
            $end = $index;
        }
        for ($i = $index; $i <= $end; $i++) {
            unset($tokens[$i]);
        }
        return new TokenCollection(array_values($tokens));
    }

    /**
     * Get token by its index
     *
     * @param $index
     * @return null|Token
     */
    public function get($index) {
        return isset($this->tokens[$index]) ? $this->tokens[$index] : null;
    }
}
