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
 * Formater for cli usage
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
interface FormaterInterface {

    /**
     * Push resultset
     *
     * @param ResultSet $resultSet
     */
    public function pushResult(ResultSet $resultSet);

    /**
     * Terminate process
     *
     * @return void
     */
    public function terminate();
}