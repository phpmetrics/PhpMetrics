<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Formater\Violations;
use Hal\Application\Extension\ExtensionService;
use Hal\Application\Formater\FormaterInterface;
use Hal\Application\Rule\Validator;
use Hal\Component\Bounds\BoundsInterface;
use Hal\Component\Result\ResultCollection;


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
     * @var ExtensionService
     */
    private $extensionsService;

    /**
     * Constructor
     *
     * @param Validator $validator
     * @param BoundsInterface $bound
     * @param ExtensionService $extensionService
     */
    public function __construct(Validator $validator, BoundsInterface $bound, ExtensionService $extensionService)
    {
        $this->bound = $bound;
        $this->validator = $validator;
        $this->extensionsService = $extensionService;
    }

    /**
     * @inheritdoc
     */
    public function terminate(ResultCollection $collection, ResultCollection $groupedResults){


        // root
        $xml = new \DOMDocument("1.0", "UTF-8");
        $xml->formatOutput = true;
        $root = $xml->createElement("pmd");
        $root->setAttribute('version', '@package_version@');
        $root->setAttribute('timestamp', date('c'));

        // violations
        foreach($collection as $item) {
            $file = $xml->createElement('file');
            $file->setAttribute('name', realpath($item->getFilename()));

            $array = $item->asArray();
            $hasViolation = false;
            foreach($array as $key => $value) {
                $result = $this->validator->validate($key, $value);
                if(Validator::GOOD !== $result && Validator::UNKNOWN !== $result) {
                    $hasViolation = true;
                    $violation = $xml->createElement('violation');
                    $violation->setAttribute('beginline' , 1);
                    $violation->setAttribute('endline', $array['loc']);
                    $violation->setAttribute('rule', $key);
                    $violation->setAttribute('ruleset', $key);
                    $violation->setAttribute('externalInfoUrl', 'http://phpmetrics.org/documentation/index.html');
                    $violation->setAttribute('priority', $result == Validator::WARNING ? 3 : 1);

                    $violation->nodeValue = sprintf('the "%1$s" value (%2$s) of "%3$s" is incorrect. The configured %1$s threshold is %4$s'
                    , $key
                    , $value
                    , $item->getName()
                    , implode(', ', $this->validator->getRuleSet()->getRule($key))
                    );
                    $file->appendChild($violation);
                }
            }

            if($hasViolation) {
                $root->appendChild($file);
            }
        }

        $xml->appendChild($root);
        return $xml->saveXML();
    }

    /**
     * @inheritdoc
     */
    public function getName() {
        return 'Violations XML';
    }
}