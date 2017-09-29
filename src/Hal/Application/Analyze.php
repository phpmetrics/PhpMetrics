<?php
/**
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application;

use Hal\Application\Config\Config;
use Hal\Application\Config\ConfigException;
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
use Hal\Metric\System\Changes\GitChanges;
use Hal\Metric\System\Coupling\Coupling;
use Hal\Metric\System\Coupling\DepthOfInheritanceTree;
use Hal\Metric\System\Coupling\PageRank;
use Hal\Metric\System\Packages\Composer\Composer;
use Hal\Metric\System\UnitTesting\UnitTesting;
use PhpParser\Error;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;

/**
 * Class Analyze
 *
 * Entry-point for the file analyze part. The responsibility of this class is to ensure all analysis are done correctly.
 *
 * @package Hal\Application
 */
class Analyze
{
    /** @var Config Configuration object that determines how the analysis must be done. */
    private $config;

    /** @var Output Output instance that is used to make some output. */
    private $output;

    /** @var Issuer Object instance that takes care about exceptions and issues that may occurs during the analysis. */
    private $issuer;

    /**
     * Analyze constructor.
     *
     * @param Config $config Configuration object that determines how the analysis must be done.
     * @param Output $output Output instance that is used to make some output.
     * @param Issuer $issuer Takes care about exceptions and issues that may occurs during the analysis.
     */
    public function __construct(Config $config, Output $output, Issuer $issuer)
    {
        $this->output = $output;
        $this->config = $config;
        $this->issuer = $issuer;
    }

    /**
     * Runs analyze.
     *
     * @param string[] $files List of file names we want to run the analysis.
     * @return Metrics
     * @throws ConfigException
     */
    public function run(array $files)
    {
        // Ensure configuration about XDebug is ok.
        \ini_set('xdebug.max_nesting_level', 3000);

        $metrics = new Metrics();

        // Traverse all files.
        $whenToStop = function () {
            return true;
        };

        // Prepare the parser and set all visitors we need to get through.
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $nodeTraverse = (new NodeTraverser(false, $whenToStop))
            ->addVisitor(new NameResolver())
            // First metrics visitor must be ClassEnumVisitor to register the element in the metrics system.
            ->addVisitor(new ClassEnumVisitor($metrics))
            ->addVisitor(new CyclomaticComplexityVisitor($metrics))
            ->addVisitor(new ExternalsVisitor($metrics))
            ->addVisitor(new LcomVisitor($metrics))
            ->addVisitor(new HalsteadVisitor($metrics))
            ->addVisitor(new LengthVisitor($metrics))
            ->addVisitor(new MaintainabilityIndexVisitor($metrics))
            ->addVisitor(new KanDefectVisitor($metrics))
            ->addVisitor(new SystemComplexityVisitor($metrics));

        // create a new progress bar (50 units)
        $progress = new ProgressBar($this->output, \count($files));
        $progress->start();

        foreach ($files as $file) {
            $progress->advance();
            $code = \file_get_contents($file);
            $this->issuer->set('filename', $file);
            try {
                $stmts = $parser->parse($code);
                $this->issuer->set('statements', $stmts);
                $nodeTraverse->traverse($stmts);
            } catch (Error $e) {
                $this->output->writeln(\sprintf('<error>Cannot parse %s</error>', $file));
            }
            $this->issuer->clear('filename');
            $this->issuer->clear('statements');
        }

        $progress->clear();

        $this->output->write('Executing system analyzes...');

        // System analyses
        (new PageRank())->calculate($metrics);
        (new Coupling())->calculate($metrics);
        (new DepthOfInheritanceTree())->calculate($metrics);

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
