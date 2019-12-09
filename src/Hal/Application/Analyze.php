<?php
namespace Hal\Application;

use Hal\Application\Config\Config;
use Hal\Component\Ast\NodeTraverser;
use Hal\Component\Issue\Issuer;
use Hal\Component\Output\Output;
use Hal\Component\Output\ProgressBar;
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
use Hal\Metric\Package\PackageAbstraction;
use Hal\Metric\Package\PackageCollectingVisitor;
use Hal\Metric\Package\PackageDependencies;
use Hal\Metric\Package\PackageDistance;
use Hal\Metric\Package\PackageInstability;
use Hal\Metric\System\Changes\GitChanges;
use Hal\Metric\System\Coupling\Coupling;
use Hal\Metric\System\Coupling\DepthOfInheritanceTree;
use Hal\Metric\System\Coupling\PageRank;
use Hal\Metric\System\Packages\Composer\Composer;
use Hal\Metric\System\UnitTesting\UnitTesting;
use PhpParser\Error;
use PhpParser\ParserFactory;

/**
 * @package Hal\Application
 */
class Analyze
{

    /**
     * @var Output
     */
    private $output;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Issuer
     */
    private $issuer;

    /**
     * @param Output $output
     */
    public function __construct(Config $config, Output $output, Issuer $issuer)
    {
        $this->output = $output;
        $this->config = $config;
        $this->issuer = $issuer;
    }

    /**
     * Runs analyze
     */
    public function run($files)
    {
        // config
        ini_set('xdebug.max_nesting_level', 3000);

        $metrics = new Metrics();

        // traverse all
        $whenToStop = function () {
            return true;
        };

        // prepare parser
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $traverser = new NodeTraverser(false, $whenToStop);
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
        $traverser->addVisitor(new PackageCollectingVisitor($metrics));

        // create a new progress bar (50 units)
        $progress = new ProgressBar($this->output, count($files));
        $progress->start();

        foreach ($files as $file) {
            $progress->advance();
            $code = file_get_contents($file);
            $this->issuer->set('filename', $file);
            try {
                $stmts = $parser->parse($code);
                $this->issuer->set('statements', $stmts);
                $traverser->traverse($stmts);
            } catch (Error $e) {
                $this->output->writeln(sprintf('<error>Cannot parse %s</error>', $file));
            }
            $this->issuer->clear('filename');
            $this->issuer->clear('statements');
        }

        $progress->clear();

        $this->output->write('Executing system analyzes...');

        //
        // System analyses
        (new PageRank())->calculate($metrics);
        (new Coupling())->calculate($metrics);
        (new DepthOfInheritanceTree())->calculate($metrics);

        //
        // Package analyses
        (new PackageDependencies())->calculate($metrics);
        (new PackageAbstraction())->calculate($metrics);
        (new PackageInstability())->calculate($metrics);
        (new PackageDistance())->calculate($metrics);

        //
        // File analyses
        (new GitChanges($this->config, $files))->calculate($metrics);

        //
        // Unit test
        (new UnitTesting($this->config, $files))->calculate($metrics);

        //
        // Composer
        (new Composer($this->config, $files))->calculate($metrics);

        $this->output->clearln();

        return $metrics;
    }
}
