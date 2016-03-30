<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Config;
use Hal\Component\Chart\Graphviz;
use Hal\Component\Config\Hydrator;
use Hal\Component\Config\Loader;
use Hal\Component\Config\Validator;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Config checker
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class ConfigValidator
{
    /**
     * @param Configuration $config
     */
    public function validates(Configuration $config)
    {
        // graphviz
        if($config->getLogging()->getChart('bubbles')) {
            $graphviz = new Graphviz();
            if(!$graphviz->isAvailable()) {
                throw new \RuntimeException('Graphviz not installed');
            }
        }
    }
}