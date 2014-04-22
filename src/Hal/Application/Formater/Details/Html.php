<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Formater\Details;
use Hal\Application\Formater\FormaterInterface;
use Hal\Application\Formater\Twig\FormatingExtension;
use Hal\Component\Result\ResultCollection;
use Hal\Application\Rule\Validator;


/**
 * Formater for html export
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Html implements FormaterInterface {

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
     */
    public function __construct(Validator $validator) {
        $this->validator = $validator;
    }

    /**
     * @inheritdoc
     */
    public function terminate(ResultCollection $collection){
        \Twig_Autoloader::register();
        $loader = new \Twig_Loader_Filesystem(__DIR__.'/../../../../../templates/html');
        $twig = new \Twig_Environment($loader, array('cache' => false));
        $twig->addExtension(new FormatingExtension($this->validator));

        return $twig->render('details/report.html.twig', array(
            'keys' => array_keys(current($collection->asArray()))
            , 'results' => $collection->asArray()
        ));
    }

    /**
     * @inheritdoc
     */
    public function getName() {
        return 'Detailed HTML';
    }
}