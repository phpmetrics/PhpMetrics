<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Formater;
use Hal\Component\Result\ResultCollection;


/**
 * Formater for cli usage
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
interface FormaterInterface {


    /**
     * Terminate process
     *
     * @param ResultCollection $collection
     * @param ResultCollection $groupedResults
     * @return string
     */
    public function terminate(ResultCollection $collection, ResultCollection $groupedResults);

    /**
     * Get name of formater
     *
     * @return string
     */
    public function getName();
}