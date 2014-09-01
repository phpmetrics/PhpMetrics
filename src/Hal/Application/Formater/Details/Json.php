<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Formater\Details;

use Hal\Application\Formater\FormaterInterface;
use Hal\Component\Result\ResultCollection;

/**
 * json formatting class for metrics result
 *
 * @package Hal\Application\Formater\Summary
 * @author marc aschmann <maschmann@gmail.com>
 */
class Json implements FormaterInterface {

    /**
     * @var boolean
     */
    private $prettyPrint;

    /**
     * Constructor
     *
     * @param boolean $prettyPrint optional pretty printing for result json
     */
    public function __construct($prettyPrint = false)
    {
        $this->prettyPrint = $prettyPrint;
    }

    /**
     * Terminate process
     *
     * @param ResultCollection $collection
     * @param ResultCollection $groupedResults
     * @return string
     */
    public function terminate(ResultCollection $collection, ResultCollection $groupedResults)
    {
        // use pretty print for readability if according php version given
        if ($this->prettyPrint && version_compare(PHP_VERSION, '5.4.0') >= 0) {
            return json_encode($collection->asArray(), JSON_PRETTY_PRINT);
        } else {
            return json_encode($collection->asArray());
        }
    }

    /**
     * Get name of formatter
     *
     * @return string
     */
    public function getName()
    {
        return 'JSON';
    }
}
