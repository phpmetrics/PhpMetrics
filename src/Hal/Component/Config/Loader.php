<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Config;
use Hal\Application\Rule\RuleSet;
use Symfony\Component\Yaml\Yaml;

/**
 * Load a config file
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Loader
{
    /**
     * Validator of configuration
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
     * Load config file
     *
     * @param $filename
     * @return array
     * @throws \RuntimeException
     */
    public function load($filename) {

        if(!\file_exists($filename) ||!\is_readable($filename)) {
            throw new \RuntimeException('configuration file is not accessible');
        }
        $content = file_get_contents($filename);
        $parser = new Yaml();
        $array = $parser->parse($content);

        // check configuration
        $array = $this->validator->validates($array);

        $config = new Configuration;
        isset($array['rules']) && $config->setRuleSet(new RuleSet( (array) $array['rules']));
        isset($array['failure']) && $config->setFailureCondition($array['failure']);
        return $config;
    }
}