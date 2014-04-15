<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Token;

/**
 * Representation of Token
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Token {
    /**
     * Type of token
     *
     * @var integer
     */
    private $type;

    /**
     * Value of token
     *
     * @var integer
     */
    private $value;

    /**
     * Constructor
     * @param string|array $data
     */
    public function __construct( $data)
    {
        if(!is_array($data)) {
            $this->type = T_STRING;
            $this->value = $data;
        } else {
            $this->type = $data[0];
            $this->value = isset($data[1]) ? $data[1] : null;
        }
    }

    /**
     * Get the type of token
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get value of token
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * String representation
     *
     * @return string
     */
    public function asString() {
        return $this->getValue();
    }

}