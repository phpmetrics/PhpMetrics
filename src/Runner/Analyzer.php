<?php declare(strict_types=1);

namespace Phpmetrix\Runner;

use Hal\Application\Config\Config;
use Hal\Component\Output\CliOutput;
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
use Hal\Metric\System\Coupling\Coupling;
use Hal\Metric\System\Coupling\DepthOfInheritanceTree;
use Hal\Metric\System\Coupling\PageRank;
use Hal\Report\Cli\Reporter;
use Hal\Violation\ViolationParser;
use Phpmetrix\Console\CliInput;
use Phpmetrix\Parser\PhpParser;
use RuntimeException;
use Symfony\Component\Finder\Finder;

final class Analyzer implements TaskExecutor
{

    private $parser;

    private $metrics;

    public function __construct(PhpParser $parser, Metrics $metrics)
    {
        $this->parser = $parser;
        $this->metrics = $metrics;
    }

    /**
     * @throws \RuntimeException
     * @throws \Phpmetrix\Parser\ParserException
     */
    public function process(CliInput $input) : void
    {
        $this->addVisitors($this->metrics);

        $files = new Finder();
        $files->in($input->directories());
        $files->notPath($input->excludePaths());
        $files->name($input->filenames());

        $progress = '';
        $time = hrtime(true);
        foreach ($files->files() as $item) {
            $progress .= '#';
            printf("\r%s", $progress);
            $this->parser->parse($item);
        }
        printf("\n");
        $this->startAnalysis($this->metrics);

        $output = new CliOutput();
        $config = new Config();
        (new Reporter($config, $output))->generate($this->metrics);

        printf("\nCompleted in %f ms\n", (hrtime(true) - $time) / (10 ** 6));
    }

    private function addVisitors(Metrics $metrics) : void
    {
        $this->parser->addVisitor(new ClassEnumVisitor($metrics));
        $this->parser->addVisitor(new CyclomaticComplexityVisitor($metrics));
        $this->parser->addVisitor(new ExternalsVisitor($metrics));
        $this->parser->addVisitor(new LcomVisitor($metrics));
        $this->parser->addVisitor(new HalsteadVisitor($metrics));
        $this->parser->addVisitor(new LengthVisitor($metrics));
        $this->parser->addVisitor(new MaintainabilityIndexVisitor($metrics));
        $this->parser->addVisitor(new KanDefectVisitor($metrics));
        $this->parser->addVisitor(new SystemComplexityVisitor($metrics));
        $this->parser->addVisitor(new PackageCollectingVisitor($metrics));
    }

    private function startAnalysis(Metrics $metrics) : void
    {
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

        (new ViolationParser())->apply($metrics);
    }
}
