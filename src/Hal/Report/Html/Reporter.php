<?php
namespace Hal\Report\Html;

use Hal\Application\Config\Config;
use Hal\Metric\ClassMetric;
use Hal\Metric\FunctionMetric;
use Hal\Metric\InterfaceMetric;
use Hal\Metric\Metrics;
use Symfony\Component\Console\Output\OutputInterface;

class Reporter
{

    /**
     * @var Config
     */
    private $config;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * Reporter constructor.
     * @param Config $config
     * @param OutputInterface $output
     */
    public function __construct(Config $config, OutputInterface $output)
    {
        $this->config = $config;
        $this->output = $output;
    }


    public function generate(Metrics $metrics)
    {

        $logDir = $this->config->get('report-html');
        if (!$logDir) {
            return;
        }

        // grouping results
        $classes = [];
        $functions = [];
        $nbInterfaces = 0;
        foreach ($metrics->all() as $key => $item) {
            if ($item instanceof ClassMetric) {
                $classes[] = $item->all();;
            }
            if ($item instanceof InterfaceMetric) {
                $nbInterfaces++;
            }
            if ($item instanceof FunctionMetric) {
                $functions[$key] = $item->all();;
            }
        }

        // sums
        $sum = (object)[
            'loc' => 0,
            'cloc' => 0,
            'lloc' => 0,
            'nbMethods' => 0,
        ];
        $avg = (object) [
            'ccn' => [],
            'bugs' => [],
            'kanDefect' => [],
            'relativeSystemComplexity' => [],
            'relativeDataComplexity' => [],
            'relativeStructuralComplexity' => [],
        ];
        foreach ($metrics->all() as $key => $item) {
            $sum->loc += $item->get('loc');
            $sum->lloc += $item->get('lloc');
            $sum->cloc += $item->get('cloc');
            $sum->nbMethods += $item->get('nbMethods');

            foreach($avg as $k=> &$a) {
                array_push($avg->$k, $item->get($k));
            }
        }
        $sum->nbClasses = sizeof($classes) - $nbInterfaces;
        $sum->nbInterfaces = $nbInterfaces;

        foreach($avg as &$a) {
            if(sizeof($a) > 0) {
                $a = round(array_sum($a) / sizeof($a), 2);
            } else {
                $a = 0;
            }
        }

        // copy sources
        if (!file_exists($logDir . '/js')) {
            mkdir($logDir, 0755, true);
            mkdir($logDir . '/js', 0755, true);
            mkdir($logDir . '/json', 0755, true);
            mkdir($logDir . '/css', 0755, true);
            mkdir($logDir . '/images', 0755, true);
        }
        recurse_copy(__DIR__ . '/template/js', $logDir . '/js');
        recurse_copy(__DIR__ . '/template/css', $logDir . '/css');
        recurse_copy(__DIR__ . '/template/images', $logDir . '/images');

        // render dynamic pages
        $this->renderPage(__DIR__ . '/template/index.php', $logDir . '/index.html', $classes, $sum, $avg);
        $this->renderPage(__DIR__ . '/template/loc.php', $logDir . '/loc.html', $classes, $sum, $avg);
        $this->renderPage(__DIR__ . '/template/relations.php', $logDir . '/relations.html', $classes, $sum, $avg);
        $this->renderPage(__DIR__ . '/template/coupling.php', $logDir . '/coupling.html', $classes, $sum, $avg);
        $this->renderPage(__DIR__ . '/template/all.php', $logDir . '/all.html', $classes, $sum, $avg);
        $this->renderPage(__DIR__ . '/template/oop.php', $logDir . '/oop.html', $classes, $sum, $avg);
        $this->renderPage(__DIR__ . '/template/complexity.php', $logDir . '/complexity.html', $classes, $sum, $avg);


        // json data
        file_put_contents(
            $logDir . '/json/classes.js',
            'var classes = ' . json_encode($classes, JSON_PRETTY_PRINT)
        );

        $this->output->writeln(sprintf('HTML report generated in "%s" directory', $logDir), OutputInterface::OUTPUT_NORMAL);

    }

    /**
     * @param $source
     * @param $destination
     * @return $this
     */
    public function renderPage($source, $destination, $classes, $sum, $avg)
    {
        ob_start();
        require $source;
        $content = ob_get_clean();
        file_put_contents($destination, $content);
        return $this;
    }
}
