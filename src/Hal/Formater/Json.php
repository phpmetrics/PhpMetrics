<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Formater;
use Hal\Result\ResultSet;


/**
 * Formater for json export
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Json implements FormaterInterface {

    /**
     * Results
     * @var array
     */
    private $results = array();

    /**
     * @inheritdoc
     */
    public function pushResult(ResultSet $resultSet) {
        $this->results[$resultSet->getFilename()] = $resultSet->asArray();
    }

    /**
     * @inheritdoc
     */
    public function terminate(){
        echo json_encode($this->results);
    }
}