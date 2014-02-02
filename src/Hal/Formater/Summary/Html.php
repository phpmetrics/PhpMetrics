<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Formater\Summary;
use Hal\Formater\FormaterInterface;
use Hal\Formater\Twig\FormatingExtension;
use Hal\Result\ResultBoundary;
use Hal\Result\ResultCollection;
use Hal\Result\ResultSet;


/**
 * Formater for html export
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Html implements FormaterInterface {

    /**
     * @inheritdoc
     */
    public function pushResult(ResultSet $resultSet) {
    }

    /**
     * @inheritdoc
     */
    public function terminate(ResultCollection $collection){

    }
}