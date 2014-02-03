<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Formater\Summary;
use Hal\Bounds\Bounds;
use Hal\Bounds\DirectoryBounds;
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
     * Level
     *
     * @var int
     */
    private $level;

    /**
     * Validator
     *
     * @var Validator
     */
    private $validator;

    /**
     * Constructor
     *
     * @param $level
     */
    public function __construct(Validator $validator, $level)
    {
        $this->level = (int) $level;
        $this->validator = $validator;
    }

    /**
     * @inheritdoc
     */
    public function terminate(ResultCollection $collection){

        $cBounds = new Bounds();
        $cDirectoryBounds = new DirectoryBounds($this->level);

        $bounds = $cBounds->calculate($collection);
        $directoryBounds = $cDirectoryBounds->calculate($collection);

        // root
        $xml = new \DOMDocument("1.0", "UTF-8");
        $xml->formatOutput = true;
        $root = $xml->createElement( "project");
        $this->injectsBounds($root, $bounds, 'average');

        // modules
        $modules = $xml->createElement('modules');
        foreach($directoryBounds as $bound) {
            $module = $xml->createElement('module');
            $this->injectsBounds($module, $bound, 'average');
            $modules->appendChild($module);
        }

        $xml->appendChild($root);
        $xml->appendChild($modules);

        return $xml->saveXML();
    }

    /**
     * Injects bound in node
     *
     * @param \DOMElement $node
     * @param BoundsResult $bound
     * @param string $type
     */
    private function injectsBounds(\DOMElement $node, ResultInterface $bound, $type) {
        $boundsAsArray = $bound->asArray();
        foreach($boundsAsArray[$type] as $k => $v) {
            $node->setAttribute($type.'-'.$k, round($v,2));
        }
    }

    /**
     * @inheritdoc
     */
    public function getName() {
        return 'Summary XML';
    }
}