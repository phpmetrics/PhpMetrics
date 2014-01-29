<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Formater;
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
     * Results
     * @var array
     */
    private $results;

    /**
     * Constructor
     */
    public function __construct() {
        $this->results = new ResultCollection();
    }

    /**
     * @inheritdoc
     */
    public function pushResult(ResultSet $resultSet) {
        $this->results[$resultSet->getFilename()] = $resultSet;
    }

    /**
     * @inheritdoc
     */
    public function terminate(){
        \Twig_Autoloader::register();
        $loader = new \Twig_Loader_Filesystem(__DIR__.'/../../../templates/html');
        $twig = new \Twig_Environment($loader, array('cache' => false));
        return $twig->render('report.html.twig', array(
            'results' => $this->results->asArray()
            , 'boundaries' => new ResultBoundary($this->results)
        ));
    }
}