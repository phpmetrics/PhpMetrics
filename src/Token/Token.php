<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Token;

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
     * Constructor
     * @param string|array $data
     */
    public function __construct( $data)
    {
        if(!is_array($data)) {
            $data = array($data);
        }
        $this->type = $data[0];
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
}