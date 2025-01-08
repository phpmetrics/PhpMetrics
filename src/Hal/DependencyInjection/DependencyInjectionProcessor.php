<?php
declare(strict_types=1);

namespace Hal\DependencyInjection;

use Closure;
use Hal\Application\Analyzer;
use Hal\Application\ApplicationFactory;
use Hal\Application\ApplicationInterface;
use Hal\Application\Bootstrap;
use Hal\Application\Config\File\ConfigFileReaderFactory;
use Hal\Application\Config\Parser;
use Hal\Application\Config\Validator;
use Hal\Application\PhpMetrics;
use Hal\Application\ReporterHandler;
use Hal\Application\VersionInfo;
use Hal\Application\Workflow;
use Hal\Component\Ast\NodeTraverser;
use Hal\Component\File\Finder;
use Hal\Component\File\Reader;
use Hal\Component\File\System;
use Hal\Component\File\Writer;
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
use Hal\Metric\Helper\RegisterMetricsVisitor;
use Hal\Metric\Helper\RoleOfMethodDetector;
use Hal\Metric\Helper\SimpleNodeIterator;
use Hal\Metric\Metrics;
use Hal\Metric\Package\PackageAbstraction;
use Hal\Metric\Package\PackageCollectingVisitor;
use Hal\Metric\Package\PackageDependencies;
use Hal\Metric\Package\PackageDistance;
use Hal\Metric\Package\PackageInstability;
use Hal\Metric\Searches\Searches;
use Hal\Metric\System\Coupling\Coupling;
use Hal\Metric\System\Coupling\DepthOfInheritanceTree;
use Hal\Metric\System\Packages\Composer\Composer;
use Hal\Metric\System\Packages\Composer\Packagist;
use Hal\Report;
use Hal\Search\SearchesValidator;
use Hal\Search\SearchInterface;
use Hal\Violation\Checkers\NoCriticalViolationsAllowed;
use Hal\Violation\Class_;
use Hal\Violation\Package;
use Hal\Violation\Search\SearchShouldNotBeFoundPrinciple;
use Hal\Violation\ViolationParser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter;
use function dirname;

/**
 * Class that injects everything and loads up.
 * This class is the technical entrypoint and thus, can't be unit tested.
 * The behavior of this class is tested via functional tests.
 *
 * @infection-ignore-all Technical class that can only be tested functionally.
 * @codeCoverageIgnore Technical class that can only be tested functionally.
 */
final class DependencyInjectionProcessor
{
    /** @var Closure(array<string>): ApplicationInterface */
    private Closure $dependencyInjectionLoader;

    public function __construct()
    {
        $this->dependencyInjectionLoader = static function (array $cliArguments): ApplicationInterface {
            $fileSystem = new System();
            $fileReader = new Reader();
            $fileWriter = new Writer();

            $versionInfo = new VersionInfo($fileReader);
            $versionInfo->inferVersionFromSemver(dirname(__DIR__, 3) . '/.semver');

            $output = new CliOutput();
            $config = (
                new Bootstrap(
                    new Parser(new ConfigFileReaderFactory($fileReader)),
                    new Validator(new SearchesValidator(), $fileSystem),
                    $output
                )
            )->prepare($cliArguments);
            $app = (new ApplicationFactory($output))->buildFromConfig($config);
            if (null !== $app) {
                return $app;
            }

            $metrics = new Metrics();
            $traverser = new NodeTraverser();
            // TODO: Maybe use PHP Attributes to leaveNode/enterNode methods on visitor to restrict the execution by
            //       Node type (ClassLike, Function, etc...)
            $traverser->addVisitor(new NameResolver());
            $traverser->addVisitor(new RegisterMetricsVisitor($metrics));
            $traverser->addVisitor(new ClassEnumVisitor($metrics));
            $traverser->addVisitor(new ExternalsVisitor($metrics, new SimpleNodeIterator()));
            $traverser->addVisitor(new LcomVisitor(
                $metrics,
                new SimpleNodeIterator(),
                new RoleOfMethodDetector(new SimpleNodeIterator())
            ));
            $traverser->addVisitor(new HalsteadVisitor($metrics, new SimpleNodeIterator()));
            $traverser->addVisitor(new LengthVisitor($metrics, new PrettyPrinter\Standard()));
            $traverser->addVisitor(new CyclomaticComplexityVisitor(
                $metrics,
                new RoleOfMethodDetector(new SimpleNodeIterator())
            ));
            $traverser->addVisitor(new MaintainabilityIndexVisitor($metrics));
            $traverser->addVisitor(new KanDefectVisitor($metrics, new SimpleNodeIterator()));
            $traverser->addVisitor(new SystemComplexityVisitor($metrics, new SimpleNodeIterator()));
            $traverser->addVisitor(new PackageCollectingVisitor($metrics));

            /**
             * @var array{
             *     files: array<string>,
             *     extensions: array<string>,
             *     exclude: array<string>,
             *     composer: bool,
             *     searches: array<SearchInterface>
             * } $configuration
             */
            $configuration = [
                'files' => $config->get('files'),
                'extensions' => $config->get('extensions'),
                'exclude' => $config->get('exclude'),
                'composer' => $config->get('composer'),
                'searches' => $config->get('searches'),
            ];
            return new PhpMetrics(
                new Analyzer(
                    $configuration['files'],
                    new Finder($configuration['extensions'], $configuration['exclude']),
                    new Workflow\WorkflowHandler(
                        $metrics,
                        new Workflow\Task\PrepareParserTask(
                            (new ParserFactory())->createForNewestSupportedVersion(),
                            $traverser,
                            $output,
                            $fileReader
                        ),
                        new Workflow\Task\AnalyzerTask(
                            // System analyses
                            new Coupling($metrics),
                            new DepthOfInheritanceTree($metrics),
                            // Package analyses
                            new PackageDependencies($metrics),
                            new PackageAbstraction($metrics),
                            new PackageInstability($metrics),
                            new PackageDistance($metrics),
                            // Composer
                            new Composer(
                                $metrics,
                                $configuration['composer'],
                                $configuration['files'],
                                new Finder(['json'], $configuration['exclude']), // Finder for composer.json
                                new Finder(['lock'], $configuration['exclude']), // Finder for composer.lock
                                $fileReader,
                                new Packagist($fileReader)
                            ),
                            new Searches($metrics, $configuration['searches'])
                        ),
                        $output
                    ),
                    new ViolationParser(
                        new Class_\Blob(),
                        new Class_\TooComplexClassCode(),
                        new Class_\TooComplexMethodCode(),
                        new Class_\ProbablyBugged(),
                        new Class_\TooLong(),
                        new Class_\TooDependent(),
                        new Package\StableAbstractionsPrinciple(),
                        new Package\StableDependenciesPrinciple(),
                        new SearchShouldNotBeFoundPrinciple()
                    ),
                ),
                new ReporterHandler(
                    new Report\Cli\Reporter(new Report\Cli\SummaryWriter($config), $output),
                    new Report\Cli\SearchReporter($config, $output),
                    new Report\Html\Reporter(
                        $config,
                        $output,
                        $fileWriter,
                        $fileReader,
                        new Report\Html\ViewHelper()
                    ),
                    new Report\Csv\Reporter($config, $output, $fileWriter),
                    new Report\Json\Reporter($config, $output, $fileWriter),
                    new Report\Json\SummaryReporter(
                        new Report\Json\SummaryWriter($config, $fileWriter),
                        $fileWriter
                    ),
                    new Report\OpenMetrics\Reporter(
                        new Report\OpenMetrics\SummaryWriter($config, $fileWriter),
                        $fileWriter
                    ),
                    new Report\Violations\Xml\Reporter($config, $output, $fileWriter),
                ),
                new NoCriticalViolationsAllowed($metrics),
                $output
            );
        };
    }

    /**
     * Load the whole application.
     *
     * @param array<string> $cliArguments
     * @return ApplicationInterface
     */
    public function load(array $cliArguments): ApplicationInterface
    {
        return ($this->dependencyInjectionLoader)($cliArguments);
    }
}
