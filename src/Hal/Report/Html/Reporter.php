<?php
namespace Hal\Report\Html;

use Hal\Application\Config\Config;
use Hal\Component\Output\Output;
use Hal\Metric\Consolidated;
use Hal\Metric\Metrics;

class Reporter
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Output
     */
    private $output;

    /**
     * @var string
     */
    protected $templateDir;

    /**
     * @param Config $config
     * @param Output $output
     */
    public function __construct(Config $config, Output $output)
    {
        $this->config = $config;
        $this->output = $output;
        $this->templateDir = __DIR__ . '/../../../../templates';
    }


    public function generate(Metrics $metrics)
    {
        $logDir = $this->config->get('report-html');
        if (!$logDir) {
            return;
        }

        // consolidate
        $consolidated = new Consolidated($metrics);

        // history of builds
        $today = (object)[
            'avg' => $consolidated->getAvg(),
            'sum' => $consolidated->getSum()
        ];
        $files = glob($logDir . '/js/history-*.json');
        $next = count($files) + 1;
        $history = [];
        natsort($files);
        foreach ($files as $filename) {
            array_push($history, json_decode(file_get_contents($filename)));
        }

        // copy sources
        if (!file_exists($logDir . '/js')) {
            mkdir($logDir . '/js', 0755, true);
        }
        if (!file_exists($logDir . '/css')) {
            mkdir($logDir . '/css', 0755, true);
        }
        if (!file_exists($logDir . '/images')) {
            mkdir($logDir . '/images', 0755, true);
        }
        if (!file_exists($logDir . '/fonts')) {
            mkdir($logDir . '/fonts', 0755, true);
        }
        recurse_copy($this->templateDir . '/html_report/js', $logDir . '/js');
        recurse_copy($this->templateDir . '/html_report/css', $logDir . '/css');
        recurse_copy($this->templateDir . '/html_report/images', $logDir . '/images');
        recurse_copy($this->templateDir . '/html_report/fonts', $logDir . '/fonts');

        // render dynamic pages
        $this->renderPage($this->templateDir . '/html_report/index.php', $logDir . '/index.html', $consolidated, $history);
        $this->renderPage($this->templateDir . '/html_report/loc.php', $logDir . '/loc.html', $consolidated, $history);
        $this->renderPage($this->templateDir . '/html_report/relations.php', $logDir . '/relations.html', $consolidated, $history);
        $this->renderPage($this->templateDir . '/html_report/coupling.php', $logDir . '/coupling.html', $consolidated, $history);
        $this->renderPage($this->templateDir . '/html_report/all.php', $logDir . '/all.html', $consolidated, $history);
        $this->renderPage($this->templateDir . '/html_report/oop.php', $logDir . '/oop.html', $consolidated, $history);
        $this->renderPage($this->templateDir . '/html_report/complexity.php', $logDir . '/complexity.html', $consolidated, $history);
        $this->renderPage($this->templateDir . '/html_report/panel.php', $logDir . '/panel.html', $consolidated, $history);
        $this->renderPage($this->templateDir . '/html_report/violations.php', $logDir . '/violations.html', $consolidated, $history);
        $this->renderPage($this->templateDir . '/html_report/packages.php', $logDir . '/packages.html', $consolidated, $history);
        $this->renderPage($this->templateDir . '/html_report/package_relations.php', $logDir . '/package_relations.html', $consolidated, $history);
        if ($this->config->has('git')) {
            $this->renderPage($this->templateDir . '/html_report/git.php', $logDir . '/git.html', $consolidated, $history);
        }
        $this->renderPage($this->templateDir . '/html_report/junit.php', $logDir . '/junit.html', $consolidated, $history);

        // js data
        file_put_contents(
            sprintf('%s/js/history-%d.json', $logDir, $next),
            json_encode($today, JSON_PRETTY_PRINT)
        );
        file_put_contents(
            sprintf('%s/js/latest.json', $logDir),
            json_encode($today, JSON_PRETTY_PRINT)
        );

        // json data
        file_put_contents(
            $logDir . '/js/classes.js',
            'var classes = ' . json_encode($consolidated->getClasses(), JSON_PRETTY_PRINT)
        );

        $this->output->writeln(sprintf('HTML report generated in "%s" directory', $logDir));
    }

    /**
     * @param $source
     * @param $destination
     * @return $this
     */
    public function renderPage($source, $destination, Consolidated $consolidated, $history)
    {
        $this->sum = $sum = $consolidated->getSum();
        $this->avg = $avg = $consolidated->getAvg();
        $this->classes = $classes = $consolidated->getClasses();
        $this->files = $files = $consolidated->getFiles();
        $this->project = $project = $consolidated->getProject();
        $this->packages = $packages = $consolidated->getPackages();
        $config = $this->config;
        $this->history = $history;

        ob_start();
        require $source;
        $content = ob_get_clean();
        file_put_contents($destination, $content);
        return $this;
    }

    /**
     * @param $type
     * @param $key
     * @return string
     */
    protected function getTrend($type, $key, $lowIsBetter = false, $highIsBetter = false)
    {
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

        $last = end($this->history);
        if (!isset($last->$type->$key)) {
            return '';
        }

        $oldValue = $last->$type->$key;
        $newValue = isset($this->$type->$key) ? $this->$type->$key : 0;
        if ($newValue > $oldValue) {
            $r = 'gt';
        } elseif ($newValue < $oldValue) {
            $r = 'lt';
        } else {
            $r = 'eq';
        }

        $diff = $newValue - $oldValue;
        if ($diff > 0) {
            $diff = '+' . $diff;
        }

        $goodOrBad = 'neutral';
        if ($lowIsBetter) {
            if ($newValue > $oldValue) {
                $goodOrBad = 'bad';
            } else {
                if ($newValue < $oldValue) {
                    $goodOrBad = 'good';
                }
            }
        }
        if ($highIsBetter) {
            if ($newValue > $oldValue) {
                $goodOrBad = 'good';
            } else {
                if ($newValue < $oldValue) {
                    $goodOrBad = 'bad';
                }
            }
        }

        return sprintf(
            '<span title="Last value: %s" class="progress progress-%s progress-%s">%s %s</span>',
            $oldValue,
            $goodOrBad,
            $r,
            $diff,
            $svg[$r]
        );
    }
}
