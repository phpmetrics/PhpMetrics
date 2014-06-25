<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Formater\Summary;
use Hal\Application\Formater\FormaterInterface;
use Hal\Application\Formater\Twig\FormatingExtension;
use Hal\Application\Rule\Validator;
use Hal\Component\Bounds\BoundsInterface;
use Hal\Component\Result\ResultCollection;


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
     */
    public function __construct(Validator $validator, BoundsInterface $bound)
    {
        $this->bound = $bound;
        $this->validator = $validator;
    }

    /**
     * @inheritdoc
     */
    public function terminate(ResultCollection $collection, ResultCollection $groupedResults){
        \Twig_Autoloader::register();
        $loader = new \Twig_Loader_Filesystem(__DIR__.'/../../../../../templates/html');
        $twig = new \Twig_Environment($loader, array('cache' => false));
        $twig->addExtension(new FormatingExtension($this->validator));

        $bound = $this->bound->calculate($collection);
        return $twig->render('summary/report.html.twig', array(
            'keys' => array_keys(current($collection->asArray()))
            , 'results' => $collection->asArray()
            , 'groupedResults' => $groupedResults
            , 'ruleSet' => $this->validator->getRuleSet()
            , 'bounds' => $bound
            , 'withOOP' => null !== $bound->getSum('instability')
        ));
    }

    /**
     * @inheritdoc
     */
    public function getName() {
        return 'Summary HTML';
    }
}