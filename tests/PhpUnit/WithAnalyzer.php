<?php

namespace Test\Hal\PhpUnit;

use Hal\Application\Analyze;
use Hal\Application\Config\Config;
use Hal\Component\File\Finder;
use Hal\Component\Issue\Issuer;
use Hal\Component\Output\TestOutput;
use Hal\Metric\Metrics;

trait WithAnalyzer
{
    public function analyze(array $filesToAnalyze = [])
    {
        $finder = new Finder();
        $config = new Config();
        $files = $finder->fetch($filesToAnalyze);

        // disable composer
        $config->set('composer', false);

        $output = new TestOutput();
        $issuer = new Issuer($output);
        return (new Analyze($config, $output, $issuer))->run($files);
    }
}
