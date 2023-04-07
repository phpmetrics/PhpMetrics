<?php
declare(strict_types=1);

namespace Hal\Report\Html;

use Hal\Application\Config\ConfigBagInterface;
use Hal\Component\File\ReaderInterface;
use Hal\Component\File\WriterInterface;
use Hal\Component\Output\Output;
use Hal\Metric\Consolidated;
use Hal\Metric\Group\Group;
use Hal\Metric\Metrics;
use Hal\Report\ReporterInterface;
use JsonException;
use RuntimeException;
use stdClass;
use function array_map;
use function array_values;
use function dirname;
use function end;
use function json_encode;
use function natsort;
use function ob_get_clean;
use function ob_start;
use function round;
use function rtrim;
use function sprintf;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

/**
 * This class is responsible for the report on HTML files.
 * TODO: Create a View class that manages the rendering.
 *
 * @infection-ignore-all TODO: this class must be refactored. Enable mutation testing once refactoring is OK.
 */
final class Reporter implements ReporterInterface
{
    private string $templateDir;
    /** @var array<Group> */
    private array $groups = [];
    private string|null $currentGroup = null;
    private string $assetPath = '';
    // List of shared metrics between HTML rendering system and current instance.
    private object $sharedMetrics;

    /**
     * @param ConfigBagInterface $config
     * @param Output $output
     * @param WriterInterface $fileWriter
     * @param ReaderInterface $fileReader
     * @param ViewHelper $viewHelper
     */
    public function __construct(
        private readonly ConfigBagInterface $config,
        private readonly Output $output,
        private readonly WriterInterface $fileWriter,
        private readonly ReaderInterface $fileReader,
        private readonly ViewHelper $viewHelper
    ) {
        $this->templateDir = dirname(__DIR__, 4) . '/templates/html_report/';
    }


    /**
     * {@inheritDoc}
     * @throws JsonException
     */
    public function generate(Metrics $metrics): void
    {
        /** @var null|string $logDir */
        $logDir = $this->config->get('report-html');
        if (null === $logDir) {
            return;
        }
        $logDir = rtrim($logDir, '/') . '/';
        $this->fileWriter->ensureDirectoryExists($logDir);
        if (!$this->fileWriter->isWritable($logDir)) {
            throw new RuntimeException(sprintf('Unable to write in the directory "%s"', $logDir));
        }

        // consolidate
        /** @var Group[] $groups */
        $groups = $this->config->get('groups');
        $this->groups = $groups;
        $consolidatedGroups = [];
        foreach ($groups as $group) {
            $reducedMetricsByGroup = $group->reduceMetrics($metrics);
            $consolidatedGroups[$group->name] = new Consolidated($reducedMetricsByGroup);
        }
        $consolidated = new Consolidated($metrics);

        // history of builds
        $today = (object)[
            'avg' => $consolidated->getAvg(),
            'sum' => $consolidated->getSum()
        ];
        /** @var array<string> $files */
        $files = $this->fileReader->glob($logDir . 'js/history-*.json');
        natsort($files);
        /** @var array<stdClass> $history */
        $history = array_map($this->fileReader->readJson(...), array_values($files));

        // copy sources
        $this->fileWriter->copy($this->templateDir . 'favicon.ico', $logDir . 'favicon.ico');
        $this->fileWriter->recursiveCopy($this->templateDir . 'js', $logDir . 'js');
        $this->fileWriter->recursiveCopy($this->templateDir . 'css', $logDir . 'css');
        $this->fileWriter->recursiveCopy($this->templateDir . 'images', $logDir . 'images');
        $this->fileWriter->recursiveCopy($this->templateDir . 'fonts', $logDir . 'fonts');
        // render dynamic pages
        $this->renderHtmlPages($logDir, $consolidated, $history);

        // js data
        $this->fileWriter->writePrettyJson($logDir . sprintf('js/history-%d.json', count($files) + 1), $today);
        $this->fileWriter->writePrettyJson($logDir . 'js/latest.json', $today);

        // consolidated by groups
        foreach ($consolidatedGroups as $name => $consolidatedGroup) {
            $this->currentGroup = $name;
            $this->assetPath = '../';

            $this->renderHtmlPages($logDir . $name . '/', $consolidatedGroup, $history);
        }

        $this->output->writeln(sprintf('HTML report generated in "%s" directory', $logDir));
    }

    /**
     * @param string $destination
     * @param Consolidated $consolidated
     * @param array<stdClass> $history
     * @return void
     * @throws JsonException
     */
    private function renderHtmlPages(string $destination, Consolidated $consolidated, array $history): void
    {
        // HTML files to generate
        $filesToGenerate = [
            'index',
            'loc',
            'relations',
            'coupling',
            'all',
            'oop',
            'complexity',
            'panel',
            'violations',
            'packages',
            'package_relations',
            'composer',
        ];

        $this->fileWriter->ensureDirectoryExists($destination);

        foreach ($filesToGenerate as $filename) {
            $this->renderPage(
                sprintf('%s%s.php', $this->templateDir, $filename),
                sprintf('%s%s.html', $destination, $filename),
                $consolidated,
                $history
            );

            $this->fileWriter->write(
                $destination . 'classes.js',
                'var classes = ' . json_encode($consolidated->getClasses(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT)
            );
        }
    }

    /**
     * @param string $source
     * @param string $destination
     * @param Consolidated $consolidated
     * @param array<int, mixed> $history
     */
    public function renderPage(string $source, string $destination, Consolidated $consolidated, array $history): void
    {
        if (!$this->fileWriter->isWritable(dirname($destination))) {
            throw new RuntimeException(sprintf('Unable to write in the directory "%s"', dirname($destination)));
        }

        $this->sharedMetrics = (object)[
            'sum' => $consolidated->getSum(),
            'avg' => $consolidated->getAvg(),
            'classes' => $consolidated->getClasses(),
            'files' => $consolidated->getFiles(),
            'project' => $consolidated->getProject(),
            'packages' => $consolidated->getPackages(),
            'config' => $this->config,
            'history' => $history,
        ];

        ob_start();
        require $source;
        /** @var string $content Cannot be false as the file is required. */
        $content = ob_get_clean();
        $this->fileWriter->write($destination, $content);
    }

    /**
     * @param string $type
     * @param string $key
     * @param bool $lowIsBetter
     * @return string
     */
    protected function getTrend(string $type, string $key, bool $lowIsBetter = false): string
    {
        if (!$this->isHomePage()) {
            return '';
        }

        $svg = [];
        $svg['gt'] = '<svg fill="#000000" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
    <path d="M16 6l2.29 2.29-4.88 4.88-4-4L2 16.59 3.41 18l6-6 4 4 6.3-6.29L22 12V6z"/>
    <path d="M0 0h24v24H0z" fill="none"/>
</svg>';
        $svg['eq'] = '<svg fill="#000000" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
    <path d="M22 12l-4-4v3H3v2h15v3z"/>
    <path d="M0 0h24v24H0z" fill="none"/>
</svg>';
        $svg['lt'] = '<svg fill="#000000" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
    <path d="M16 18l2.29-2.29-4.88-4.88-4 4L2 7.41 3.41 6l6 6 4-4 6.3 6.29L22 12v6z"/>
    <path d="M0 0h24v24H0z" fill="none"/>
</svg>';

        $last = end($this->sharedMetrics->history);
        if (!isset($last->$type->$key)) {
            return '';
        }

        $oldValue = $last->$type->$key;
        $newValue = $this->sharedMetrics->$type->$key ?? 0;
        $trendIndex = 1 + ($newValue <=> $oldValue);

        $diff = $newValue - $oldValue;

        $trendCodes = [0 => 'lt', 1 => 'eq', 2 => 'gt'];
        $trendNames = [0 => ($lowIsBetter ? 'good' : 'bad'), 1 => 'neutral', 2 => ($lowIsBetter ? 'bad' : 'good')];

        return sprintf(
            '<span title="Last value: %s" class="progress progress-%s progress-%s">%s %s</span>',
            $oldValue,
            $trendNames[$trendIndex],
            $trendCodes[$trendIndex],
            ($diff > 0) ? '+' . round($diff, 3) : round($diff, 3),
            $svg[$trendCodes[$trendIndex]]
        );
    }

    /**
     * @return bool
     */
    private function isHomePage(): bool
    {
        return null === $this->currentGroup;
    }
}
