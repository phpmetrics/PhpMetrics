<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Evaluation;

/**
 * Result of Evaluation
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Evaluation
{

    private $valid = true;

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->valid === true ? 0 : 1;
    }

    /**
     * @param boolean $valid
     * @return $this
     */
    public function setValid($valid) {
        $this->valid = $valid;
        return $this;
    }

    /**
     * @return bool
     */
    public function isValid() {
        return $this->valid;
    }
}