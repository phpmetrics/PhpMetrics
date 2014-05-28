<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Metrics\Complexity\Structural\LCOM;
use Hal\Component\Result\ExportableInterface;

/**
 * Representation of LCOM result
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Result implements ExportableInterface {

    /**
     * lack of cohesion of methods
     *
     * @var int
     */
    private $lcom;

    /**
     * @inheritdoc
     */
    public function asArray() {
        return array (
            'lcom' => $this->getLcom()
        );
    }

    /**
     * @param int $lcom
     */
    public function setLcom($lcom)
    {
        $this->lcom = $lcom;
    }

    /**
     * @return int
     */
    public function getLcom()
    {
        return $this->lcom;
    }

}