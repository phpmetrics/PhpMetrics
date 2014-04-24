<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Metrics\Mood\Instability;
use Hal\Component\Result\ExportableInterface;


/**
 * Represents Instability
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Result implements ExportableInterface {

    /**
     * @var int
     */
    private $instability;

    /**
     * @inheritdoc
     */
    public function asArray()
    {
        return array(
            'instability' => $this->getInstability()
        );
    }

    /**
     * @param int $instability
     */
    public function setInstability($instability)
    {
        $this->instability = $instability;
    }

    /**
     * @return int
     */
    public function getInstability()
    {
        return $this->instability;
    }

}