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
use Symfony\Component\Console\Input\InputInterface;

/**
 * Config factory
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class ConfigFactory
{
    /**
     * Factory config according Input
     *
     * @param InputInterface $input
     * @return Configuration
     */
    public function factory(InputInterface $input) {

        $config = new Configuration();

        $treeBuilder = new TreeBuilder();
        $hydrator = new Hydrator(new Validator($treeBuilder->getTree()));

        // first, load config file
        if(strlen($input->getOption('config')) > 0) {
            $loader = new Loader($hydrator);
            $config = $loader->load($input->getOption('config'));
        } else {
            $config = $hydrator->hydrates($config, array());
        }

        // then, overwrite configuration by arguments provided in run
        strlen($input->getArgument('path')) > 0         && $config->getPath()->setBasePath($input->getArgument('path'));
        strlen($input->getOption('extensions')) > 0     && $config->getPath()->setExtensions($input->getOption('extensions'));
        strlen($input->getOption('excluded-dirs')) > 0  && $config->getPath()->setExcludedDirs($input->getOption('excluded-dirs'));
        strlen($input->getOption('symlinks')) > 0       && $config->getPath()->setFollowSymlinks(true);
        strlen($input->getOption('report-xml')) > 0     && $config->getLogging()->setReport('xml', $input->getOption('report-xml'));
        strlen($input->getOption('report-cli')) > 0     && $config->getLogging()->setReport('cli', $input->getOption('report-cli'));
        strlen($input->getOption('report-json')) > 0    && $config->getLogging()->setReport('json', $input->getOption('report-json'));
        strlen($input->getOption('report-html')) > 0    && $config->getLogging()->setReport('html', $input->getOption('report-html'));
        strlen($input->getOption('report-csv')) > 0     && $config->getLogging()->setReport('csv', $input->getOption('report-csv'));
        strlen($input->getOption('violations-xml')) > 0 && $config->getLogging()->setViolation('xml', $input->getOption('violations-xml'));
        strlen($input->getOption('chart-bubbles')) > 0  && $config->getLogging()->setChart('bubbles', $input->getOption('chart-bubbles'));

        return $config;

    }
}