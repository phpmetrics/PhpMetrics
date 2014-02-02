<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Command;

use Exception;
use Hal\Formater\Summary;
use Hal\Formater\Details;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

/**
 * Command for run analysis
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class RunMetricsCommand extends Command
{

    /**
     * @var ProgressHelper
     */
    private $progress;

    /**
     * Files to analyze
     *
     * @var array
     */
    private $files = array();

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
                ->setName('metrics')
                ->setDescription('Run analysis')
                ->addArgument(
                        'path', InputArgument::REQUIRED, 'Path to explore'
                )
                ->addOption(
                        'summary-html',null, InputOption::VALUE_REQUIRED, 'Path to save summary report in HTML format'
                )
                ->addOption(
                        'details-html', null, InputOption::VALUE_REQUIRED, 'Path to save detailled report in HTML format'
                )
                ->addOption(
                        'level', null, InputOption::VALUE_REQUIRED, 'Depth of summary report', 3
                )
                ->addOption(
                        'extensions', null, InputOption::VALUE_REQUIRED, 'Regex of extensions to include', 'php'
                )
        ;
    }

    /**
     * Prepare procedure
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \LogicException
     */
    protected function prepare(InputInterface $input, OutputInterface $output)
    {

        $path = $input->getArgument('path');
        if(is_dir($path)) {
            $path = rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
            $directory = new \RecursiveDirectoryIterator($path);
            $iterator = new \RecursiveIteratorIterator($directory);
            $regex = new \RegexIterator($iterator, '/^.+\.('. $input->getOption('extensions') .')$/i', \RecursiveRegexIterator::GET_MATCH);
            foreach($regex as $file) {
                $this->files[] = $file[0];
            }

        } elseif(is_file($path)) {
            $this->files = array($path);
        } else {
            throw new \LogicException('No file found');
        }

        if(sizeof($this->files, COUNT_NORMAL) == 0) {
            throw new \LogicException('No file found');
        }

        $this->progress = new ProgressHelper();
        $this->progress->start($output, sizeof($this->files, COUNT_NORMAL));

    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $this->prepare($input, $output);
        $path = $input->getArgument('path');
        $level = $input->getOption('level');

        // rules
        $rules = new \Hal\Rule\RuleSet();
        $validator = new \Hal\Rule\Validator($rules);

        // CLI formater
        $summary = new Summary\Cli($validator, $output, $level);


        $collection = new \Hal\Result\ResultCollection();
        foreach($this->files as $filename) {

            $this->progress->advance();

            // calculates
            $halstead = new \Hal\Halstead\Halstead(new \Hal\Token\TokenType());
            $rHalstead = $halstead->calculate($filename);

            $loc = new \Hal\Loc\Loc();
            $rLoc = $loc->calculate($filename);

            $maintenability = new \Hal\MaintenabilityIndex\MaintenabilityIndex;
            $rMaintenability = $maintenability->calculate($rHalstead, $rLoc);

            // formats
            $resultSet = new \Hal\Result\ResultSet(basename($path) . str_replace($path, '/', $filename));
            $resultSet
                ->setLoc($rLoc)
                ->setHalstead($rHalstead)
                ->setMaintenabilityIndex($rMaintenability);

            $collection->push($resultSet);
        }
        $this->progress->clear();
        $this->progress->finish();

        $output->write($summary->terminate($collection));


        //
        // Generate reports
        $out = $input->getOption('summary-html');
        if($out) {
            $dir = dirname($out);
            if(!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            $output->writeln('Generating Summary HTML Report...');
            $handle = fopen($out, 'w');
            $stream = new StreamOutput($handle);
            $report = new Summary\Html($validator, $level);
            $stream->write($report->terminate($collection));
            fclose($handle);
        }

        $out = $input->getOption('details-html');
        if($out) {
            $dir = dirname($out);
            if(!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            $output->writeln('Generating Detailled HTML Report...');
            $handle = fopen($out, 'w');
            $stream = new StreamOutput($handle);
            $report = new Details\Html($validator, $level);
            $stream->write($report->terminate($collection));
            fclose($handle);
        }

        $output->writeln('<info>done</info>');

        return 0;
    }

}
