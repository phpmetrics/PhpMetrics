<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Formater\Summary;
use Hal\Bounds\DirectoryBounds;
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
     * Level
     *
     * @var int
     */
    private $level;

    /**
     * Constructor
     *
     * @param $level
     */
    public function __construct($level)
    {
        $this->level = (int) $level;
    }


    /**
     * @inheritdoc
     */
    public function pushResult(ResultSet $resultSet) {
    }

    /**
     * @inheritdoc
     */
    public function terminate(ResultCollection $collection){
        \Twig_Autoloader::register();
        $loader = new \Twig_Loader_Filesystem(__DIR__.'/../../../../templates/html');
        $twig = new \Twig_Environment($loader, array('cache' => false));
        $twig->addExtension(new FormatingExtension());

        $bounds = new DirectoryBounds();
        $directoryBounds = new DirectoryBounds($this->level);

        return $twig->render('summary/report.html.twig', array(
            'keys' => array_keys(current($collection->asArray()))
            , 'results' => $collection->asArray()
            , 'directoryBounds' => $directoryBounds->calculate($collection)
            , 'bounds' => $bounds->calculate($collection)
        ));
    }
}