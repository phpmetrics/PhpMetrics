<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Config;
use Hal\Application\Config\Configuration;
use Hal\Application\Config\LoggingConfiguration;
use Hal\Application\Config\PathConfiguration;
use Hal\Application\Rule\RuleSet;

/**
 * Hydrates configuration
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Hydrator
{
    /**
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
     * Hydrates configuration
     *
     * @param Configuration $config
     * @param array $array
     * @return Configuration
     */
    public function hydrates(Configuration $config, array $array) {

        $array = $this->validator->validates($array);
        $path = new PathConfiguration();
        $path
            ->setBasePath($array['path']['directory'])
            ->setExtensions($array['path']['extensions'])
            ->setExcludedDirs($array['path']['exclude'])
        ;

        $config
            ->setRuleSet(new RuleSet( (array) $array['rules']))
            ->setFailureCondition($array['failure'])
            ->setPath($path)
            ->setLogging(new LoggingConfiguration($array['logging']))
        ;
        return $config;
    }
}