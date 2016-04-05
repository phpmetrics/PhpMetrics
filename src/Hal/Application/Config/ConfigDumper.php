<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Config;
use Hal\Component\Config\Hydrator;
use Hal\Component\Config\Loader;
use Hal\Component\Config\Validator;
use Hal\Component\Result\ExportableInterface;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Config file generator
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class ConfigDumper
{
    /**
     * @var string
     */
    private $destination;

    /**
     * @var ExportableInterface
     */
    private $ruleset;

    /**
     * ConfigFileGenerator constructor.
     * @param string $destination
     * @param ExportableInterface $ruleset
     */
    public function __construct($destination, ExportableInterface $ruleset)
    {
        $this->destination = $destination;
        $this->ruleset = $ruleset;
    }

    /**
     * Generate config file
     *
     * @return $this
     */
    public function dump()
    {

        // rules
        $rules = '';
        foreach($this->ruleset->asArray() as $key => $values) {
            $rules .= sprintf('        %s: [ %s ]%s', $key, implode(', ', $values), PHP_EOL);
        }

        // main content
        $content = <<<EOT
# This file is used by PhpMetrics
# Please visit http://www.phpmetrics.org do get more informations
default:
    path:
        directory: src
    logging:
        report:
            html:   ./phpmetrics.html
    rules:
{$rules}

EOT;

        // write file
        if(!$this->destination) {
            throw new \LogicException('Please provide a destination');
        }

        $dir = dirname($this->destination);
        if(!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        $handle = fopen($this->destination, 'w');
        fwrite($handle, $content);
        fflush($handle);
        fclose($handle);

        return $this;

    }
}