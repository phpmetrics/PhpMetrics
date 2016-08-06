<?php
namespace Hal\Application;

use Hal\Metric\Class_\ClassEnumVisitor;
use Hal\Metric\Class_\Complexity\CyclomaticComplexityVisitor;
use Hal\Metric\Class_\Complexity\KanDefectVisitor;
use Hal\Metric\Class_\Component\MaintainabilityIndexVisitor;
use Hal\Metric\Class_\Coupling\ExternalsVisitor;
use Hal\Metric\Class_\Structural\LcomVisitor;
use Hal\Metric\Class_\Structural\SystemComplexityVisitor;
use Hal\Metric\Class_\Text\HalsteadVisitor;
use Hal\Metric\Class_\Text\LengthVisitor;
use Hal\Metric\Metrics;
use Hal\Metric\System\Coupling\Coupling;
use Hal\Metric\System\Coupling\PageRank;
use PhpParser\Error;
use PhpParser\ParserFactory;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Class Analyze
 * @package Hal\Application
 */
class Analyze
{

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * Analyze constructor.
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Runs analyze
     */
    public function run($files)
    {
        // config
        ini_set('xdebug.max_nesting_level', 3000);

        $metrics = new Metrics();

        // prepare parser
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $traverser = new \PhpParser\NodeTraverser();
        $traverser->addVisitor(new \PhpParser\NodeVisitor\NameResolver());
        $traverser->addVisitor(new ClassEnumVisitor($metrics));
        $traverser->addVisitor(new CyclomaticComplexityVisitor($metrics));
        $traverser->addVisitor(new ExternalsVisitor($metrics));
        $traverser->addVisitor(new LcomVisitor($metrics));
        $traverser->addVisitor(new HalsteadVisitor($metrics));
        $traverser->addVisitor(new LengthVisitor($metrics));
        $traverser->addVisitor(new CyclomaticComplexityVisitor($metrics));
        $traverser->addVisitor(new MaintainabilityIndexVisitor($metrics));
        $traverser->addVisitor(new KanDefectVisitor($metrics));
        $traverser->addVisitor(new SystemComplexityVisitor($metrics));

        // create a new progress bar (50 units)
        $progress = new ProgressBar($this->output, sizeof($files));
        $this->output->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
        $progress->start();

        foreach ($files as $file) {
            $progress->advance();
            $code = file_get_contents($file);
            try {
                $stmts = $parser->parse($code);
                $traverser->traverse($stmts);
            } catch (Error $e) {
                $this->output->writeln(sprintf('<error>Cannot parse %s</error>', $file));
            }
        }
        $progress->finish();
        $progress->clear();

        //
        // System analyses
        $this->output->writeln('Executing post analyses...');
        (new PageRank())->calculate($metrics);
        (new Coupling())->calculate($metrics);

        return $metrics;
    }
}
