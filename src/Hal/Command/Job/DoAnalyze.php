<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Command\Job;
use Hal\Command\Job\Analyze\CouplingAnalyzer;
use Hal\Command\Job\Analyze\FileAnalyzer;
use Hal\Coupling\Coupling;
use Hal\Coupling\FileCoupling;
use Hal\File\Finder;
use Hal\File\SyntaxChecker;
use Hal\OOP\Extractor\ClassMap;
use Hal\OOP\Extractor\Extractor;
use Hal\OOP\Extractor\Result;
use Hal\Result\ResultCollection;
use Hal\Token\Tokenizer;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Starts analyze
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class DoAnalyze implements JobInterface
{

    /**
     * Path to analyze
     *
     * @var string
     */
    private $path;

    /**
     * Output
     *
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    private $output;

    /**
     * Finder
     *
     * @var Finder
     */
    private $finder;

    /**
     * do OOP analyze ?
     *
     * @var bool
     */
    private $withOOP;

    /**
     * Constructor
     *
     * @param OutputInterface $output
     * @param Finder $finder
     * @param string $path
     * @param bool $withOOP
     */
    function __construct(OutputInterface $output, Finder $finder, $path, $withOOP)
    {
        $this->output = $output;
        $this->finder = $finder;
        $this->path = $path;
        $this->withOOP = $withOOP;
    }

    /**
     * @inheritdoc
     */
    public function execute(ResultCollection $collection) {

        $files = $this->finder->find($this->path);

        if(0 == sizeof($files, COUNT_NORMAL)) {
            throw new \LogicException('No file found');
        }

        $progress = new ProgressHelper();
        $progress->start($this->output, sizeof($files, COUNT_NORMAL));

        // tools
        $classMap = new ClassMap();
        $tokenizer = new Tokenizer();
        $syntaxChecker = new SyntaxChecker();

        $fileAnalyzer = new FileAnalyzer(
            $this->output
            , $this->withOOP
            , new Extractor($tokenizer)
            , new \Hal\Halstead\Halstead($tokenizer, new \Hal\Token\TokenType())
            , new \Hal\Loc\Loc($tokenizer)
            , new \Hal\MaintenabilityIndex\MaintenabilityIndex($tokenizer)
            , new \Hal\McCabe\McCabe($tokenizer)
            , $classMap
        );

        foreach($files as $filename) {

            $progress->advance();

            // Integrity
            if(!$syntaxChecker->isCorrect($filename)) {
                $this->output->writeln(sprintf('<error>file %s is not valid and has been skipped</error>', $filename));
                continue;
            }

            // Analyze
            $resultSet = $fileAnalyzer->execute($filename);
            $collection->push($resultSet);
        }

        $progress->clear();
        $progress->finish();

        if($this->withOOP) {
            // COUPLING (should be done after parsing files)
            $this->output->writeln('Analyzing coupling. This will take few minutes...');
            $couplingAnalyzer = new CouplingAnalyzer($classMap, $collection);
            $couplingAnalyzer->execute($files);
        }
    }

}
