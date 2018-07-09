<?php
namespace Hal\Application;

use Hal\Application\Config\ConfigException;
use Hal\Application\Config\Parser;
use Hal\Application\Config\Validator;
use Hal\Component\File\Finder;
use Hal\Component\Output\CliOutput;
use Hal\Component\Issue\Issuer;
use Hal\Report;
use Hal\Violation\ViolationParser;


class Application
{

    /**
     * @param $argv
     */
    public function run($argv)
    {
        // formatter
        $output = new CliOutput();

        // issues and debug
        $issuer = (new Issuer($output));//->enable();

        // config
        $config = (new Parser())->parse($argv);
        try {
            (new Validator())->validate($config);
        } catch (ConfigException $e) {

            if ($config->has('help')) {
                $output->writeln((new Validator())->help());
                exit(0);
            }

            if ($config->has('version')) {
                $output->writeln(sprintf("PhpMetrics %s <http://www.phpmetrics.org>\nby Jean-François Lépine <https://twitter.com/Halleck45>\n", getVersion()));
                exit(0);
            }

            $output->writeln(sprintf("\n<error>%s</error>\n", $e->getMessage()));
            $output->writeln((new Validator())->help());
            exit(1);
        }

        if ($config->has('quiet')) {
            $output->setQuietMode(true);
        }

        // find files
        $finder = new Finder($config->get('extensions'), $config->get('exclude'));
        $files = $finder->fetch($config->get('files'));

        // analyze
        try {
            $metrics = (new Analyze($config, $output, $issuer))->run($files);
        }catch(ConfigException $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            exit(1);
        }

        // violations
        (new ViolationParser($config, $output))->apply($metrics);

        // report
        (new Report\Cli\Reporter($config, $output))->generate($metrics);
        (new Report\Html\Reporter($config, $output))->generate($metrics);
        (new Report\Csv\Reporter($config, $output))->generate($metrics);
        (new Report\Json\Reporter($config, $output))->generate($metrics);
        (new Report\Violations\Xml\Reporter($config, $output))->generate($metrics);

        // end
        $output->writeln('');
        $output->writeln('Done');
        $output->writeln('');

    }
}
