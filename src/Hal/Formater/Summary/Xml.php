<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Formater\Summary;
use Hal\Bounds\Bounds;
use Hal\Bounds\BoundsAgregateInterface;
use Hal\Bounds\BoundsInterface;
use Hal\Bounds\Result\BoundsResult;
use Hal\Bounds\Result\ResultInterface;
use Hal\Formater\FormaterInterface;
use Hal\Result\ResultCollection;
use Hal\Rule\Validator;


/**
 * Formater for xml export
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Xml implements FormaterInterface {

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

        $bounds = $this->bound->calculate($collection);
        $directoryBounds = $this->agregateBounds->calculate($collection);

        // root
        $xml = new \DOMDocument("1.0", "UTF-8");
        $xml->formatOutput = true;
        $root = $xml->createElement("project");
        $this->injectsBounds($root, $bounds);

        // modules
        $modules = $xml->createElement('modules');
        foreach($directoryBounds as $bound) {
            $module = $xml->createElement('module');
            $this->injectsBounds($module, $bound);
            $module->setAttribute('namespace', $bound->getDirectory());
            $modules->appendChild($module);
        }

        $xml->appendChild($root);
        $root->appendChild($modules);

        return $xml->saveXML();
    }

    /**
     * Injects bound in node
     *
     * @param \DOMElement $node
     * @param ResultInterface $bound
     */
    private function injectsBounds(\DOMElement $node, ResultInterface $bound) {
        $node->setAttribute('loc', $bound->getSum('loc'));
        $node->setAttribute('lloc', $bound->getSum('logicalLoc'));
        $node->setAttribute('cyclomaticComplexity', $bound->getAverage('cyclomaticComplexity'));
        $node->setAttribute('maintenabilityIndex', $bound->getAverage('maintenabilityIndex'));
        $node->setAttribute('volume', $bound->getAverage('volume'));
        $node->setAttribute('vocabulary', $bound->getAverage('vocabulary'));
        $node->setAttribute('difficulty', $bound->getAverage('difficulty'));
        $node->setAttribute('bugs', $bound->getAverage('bugs'));
        $node->setAttribute('time', $bound->getAverage('time'));
        $node->setAttribute('intelligentContent', $bound->getAverage('intelligentContent'));
    }

    /**
     * @inheritdoc
     */
    public function getName() {
        return 'Summary XML';
    }
}