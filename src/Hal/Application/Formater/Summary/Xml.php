<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Formater\Summary;
use Hal\Component\Bounds\Bounds;
use Hal\Component\Bounds\BoundsInterface;
use Hal\Component\Bounds\Result\ResultInterface;
use Hal\Application\Formater\FormaterInterface;
use Hal\Component\Result\ResultCollection;
use Hal\Application\Rule\Validator;


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

        $bounds = $this->bound->calculate($collection);

        // root
        $xml = new \DOMDocument("1.0", "UTF-8");
        $xml->formatOutput = true;
        $root = $xml->createElement("project");
        $this->injectsBounds($root, $bounds);

        // modules
        $modules = $xml->createElement('modules');
        foreach($groupedResults as $result) {
            $module = $xml->createElement('module');
            $this->injectsBounds($module, $result->getBounds());
            $module->setAttribute('namespace', $result->getName());
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

        $hasOOP = null !== $bound->getSum('instability');
        if($hasOOP) {
            $node->setAttribute('lcom', $bound->getAverage('lcom'));
            $node->setAttribute('instability', $bound->getAverage('instability'));
            $node->setAttribute('efferentCoupling', $bound->getAverage('efferentCoupling'));
            $node->setAttribute('afferentCoupling', $bound->getAverage('afferentCoupling'));
        }
    }

    /**
     * @inheritdoc
     */
    public function getName() {
        return 'Summary XML';
    }
}