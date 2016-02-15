<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Command\Job;
use Hal\Application\Command\Job\Analyze\CardAndAgrestiAnalyzer;
use Hal\Application\Command\Job\Analyze\CouplingAnalyzer;
use Hal\Application\Command\Job\Analyze\FileAnalyzer;
use Hal\Application\Command\Job\Analyze\LcomAnalyzer;
use Hal\Component\Compatibility\PhpCompatibility;
use Hal\Component\Cache\CacheMemory;
use Hal\Component\File\Finder;
use Hal\Component\File\SyntaxChecker;
use Hal\Component\OOP\Extractor\ClassMap;
use Hal\Component\OOP\Extractor\Extractor;
use Hal\Component\Result\ResultCollection;
use Hal\Component\Token\NoTokenizableException;
use Hal\Component\Token\Tokenizer;
use Symfony\Component\Console\Helper\ProgressBar;
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
     * Ignore errors ?
     * @var bool
     */
    private $ignoreErrors;

    /**
     * Constructor
     *
     * @param OutputInterface $output
     * @param Finder $finder
     * @param string $path
     * @param bool $withOOP
     * @param bool $ignoreErrors
     */
    public function __construct(OutputInterface $output, Finder $finder, $path, $withOOP, $ignoreErrors = false)
    {
        $this->output = $output;
        $this->finder = $finder;
        $this->path = $path;
        $this->withOOP = $withOOP;
        $this->ignoreErrors = $ignoreErrors;
    }

    /**
     * @inheritdoc
     */
    public function execute(ResultCollection $collection, ResultCollection $aggregatedResults) {

        $files = $this->finder->find($this->path);

        if(0 == sizeof($files, COUNT_NORMAL)) {
            throw new \LogicException('No file found');
        }

        $progress = new ProgressBar($this->output);
        $progress->start(sizeof($files, COUNT_NORMAL));

        // tools
        $classMap = new ClassMap();
        $tokenizer = new Tokenizer(new CacheMemory());
        $syntaxChecker = new SyntaxChecker();

        $fileAnalyzer = new FileAnalyzer(
            $this->output
            , $this->withOOP
            , new Extractor($tokenizer)
            , new \Hal\Metrics\Complexity\Text\Halstead\Halstead($tokenizer, new \Hal\Component\Token\TokenType())
            , new \Hal\Metrics\Complexity\Text\Length\Loc($tokenizer)
            , new \Hal\Metrics\Design\Component\MaintainabilityIndex\MaintainabilityIndex()
            , new \Hal\Metrics\Complexity\Component\McCabe\McCabe($tokenizer)
            , new \Hal\Metrics\Complexity\Component\Myer\Myer($tokenizer)
            , $classMap
        );

        foreach($files as $k => $filename) {

            $progress->advance();

            // Integrity
            if(!$this->ignoreErrors && !$syntaxChecker->isCorrect($filename)) {
                $this->output->writeln(sprintf('<error>file %s is not valid and has been skipped</error>', $filename));
                unset($files[$k]);
                continue;
            }

            // Analyze
            try {
                $resultSet = $fileAnalyzer->execute($filename);
            } catch(NoTokenizableException $e) {
                $this->output->writeln(sprintf("<error>file %s has been skipped: \n%s</error>", $filename, $e->getMessage()));
                unset($files[$k]);
                continue;
            } catch (\Exception $e) {
                throw new \Exception(
                    (
                        sprintf("a '%s' exception occured analyzing file %s\nMessage: %s", get_class($e), $filename, $e->getMessage())
                        . sprintf("\n------------\nStack:\n%s", $e->getTraceAsString())
                        . sprintf("\n------------\nDo not hesitate to report a bug: https://github.com/PhpMetrics/PhpMetrics/issues")
                    )
                , 0, $e->getPrevious());
            }

            $collection->push($resultSet);
        }

        $progress->clear();
        $progress->finish();


        if($this->withOOP) {
            // COUPLING (should be done after parsing files)
            $this->output->write(str_pad("\x0DAnalyzing coupling. This will take few minutes...", 80, "\x20"));
            $couplingAnalyzer = new CouplingAnalyzer($classMap, $collection);
            $couplingAnalyzer->execute($files);

            // LCOM (should be done after parsing files)
            $this->output->write(str_pad("\x0DLack of cohesion of method (lcom). This will take few minutes...", 80, "\x20"));
            $lcomAnalyzer = new LcomAnalyzer($classMap, $collection);
            $lcomAnalyzer->execute($files);

            // Card and Agresti (should be done after parsing files)
            $this->output->write(str_pad("\x0DAnalyzing System complexity. This will take few minutes...", 80, "\x20"));
            $lcomAnalyzer = new CardAndAgrestiAnalyzer($classMap, $collection);
            $lcomAnalyzer->execute($files);
        }

    }

}
