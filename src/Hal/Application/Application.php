<?php
namespace Hal\Application;

use Hal\Application\Config\ConfigException;
use Hal\Application\Config\Parser;
use Hal\Application\Config\Validator;
use Hal\Component\File\Finder;
use Hal\Report;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Output\ConsoleOutput;


class Application
{

    /**
     * @param $argv
     */
    public function run($argv)
    {
        // formatter
        $output = new ConsoleOutput(ConsoleOutput::VERBOSITY_NORMAL, null, new OutputFormatter());

        // config
        $config = (new Parser())->parse($argv);
        try {
            (new Validator())->validate($config);
        } catch (ConfigException $e) {

            if ($config->has('help')) {
                $output->writeln((new Validator())->help());
                exit(1);
            }

            if ($config->has('version')) {
                $output->writeln(sprintf("PhpMetrics %s <http://phpmetrics.org>\nby Jean-François Lépine <https://twitter.com/Halleck45>", getVersion()));
                exit(1);
            }

            $output->writeln(sprintf("\n<error>%s</error>\n", $e->getMessage()));
            $output->writeln((new Validator())->help());
            exit(1);
        }

        if ($config->has('quiet')) {
            $output->setVerbosity(ConsoleOutput::VERBOSITY_QUIET);
        }

        // find files
        $finder = new Finder();
        $files = $finder->fetch($config->get('files'));

        // analyze
        $metrics = (new Analyze($output))->run($files);

        // report
        (new Report\Cli\Reporter($config, $output))->generate($metrics);
        (new Report\Html\Reporter($config, $output))->generate($metrics);

        // end
        $output->writeln('');
        $output->writeln('<info>Done</info>');
    }
}