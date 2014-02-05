<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Formater\Summary;
use Hal\Bounds\BoundsAgregateInterface;
use Hal\Bounds\BoundsInterface;
use Hal\Formater\FormaterInterface;
use Hal\Formater\Twig\FormatingExtension;
use Hal\Result\ResultCollection;
use Hal\Rule\Validator;


/**
 * Formater for html export
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Html implements FormaterInterface {

    /**
     * Bounds
     *
     * @var BoundsInterface
     */
    private $bound;

    /**
     * AgregateBounds
     *
     * @var BoundsInterface
     */
    private $agregateBounds;

    /**
     * Validator
     *
     * @var Validator
     */
    private $validator;

    /**
     * Constructor
     *
     * @param Validator $validator
     * @param BoundsInterface $bound
     * @param BoundsAgregateInterface $agregateBounds
     */
    public function __construct(Validator $validator, BoundsInterface $bound, BoundsAgregateInterface $agregateBounds)
    {
        $this->bound = $bound;
        $this->agregateBounds = $agregateBounds;
        $this->validator = $validator;
    }

    /**
     * @inheritdoc
     */
    public function terminate(ResultCollection $collection){
        \Twig_Autoloader::register();
        $loader = new \Twig_Loader_Filesystem(__DIR__.'/../../../../templates/html');
        $twig = new \Twig_Environment($loader, array('cache' => false));
        $twig->addExtension(new FormatingExtension($this->validator));

        return $twig->render('summary/report.html.twig', array(
            'keys' => array_keys(current($collection->asArray()))
            , 'results' => $collection->asArray()
            , 'directoryBounds' => $this->agregateBounds->calculate($collection)
            , 'bounds' => $this->bound->calculate($collection)
        ));
    }

    /**
     * @inheritdoc
     */
    public function getName() {
        return 'Summary HTML';
    }
}