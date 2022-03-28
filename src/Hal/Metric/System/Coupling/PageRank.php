<?php
declare(strict_types=1);

namespace Hal\Metric\System\Coupling;

use Hal\Metric\CalculableInterface;
use Hal\Metric\ClassMetric;
use Hal\Metric\Metrics;
use function abs;
use function array_fill_keys;
use function array_keys;
use function array_map;
use function round;

/**
 * Calculates the PageRank for all classes.
 * The PageRank is a value that determines how much a class is referenced by its siblings, but with addition of PageRank
 * deducted to other siblings too.
 * The more the class is called, the higher its PageRank value will be.
 * See: http://phpir.com/pagerank-in-php/ for more information.
 */
final class PageRank implements CalculableInterface
{
    public function __construct(private readonly Metrics $metrics)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function calculate(): void
    {
        // Build an array of relations.
        /** @var array<string, array<string>> $links */
        $links = [];
        array_map(static function (ClassMetric $metric) use (&$links): void {
            $links[$metric->get('name')] = $metric->get('externals');
        }, $this->metrics->getClassMetrics());

        // If no links, no PageRank to be calculated.
        if ([] === $links) {
            return;
        }

        foreach ($this->calculatePageRank($links) as $name => $rank) {
            /** @var ClassMetric $class */
            $class = $this->metrics->get($name);
            $class->set('pageRank', round($rank, 2));
        }
    }

    /**
     * Calculates the PageRank based on similar algorithm that Google were using for original PageRank script.
     *
     * @param non-empty-array<string, array<string>> $linkGraph
     * @return non-empty-array<string, float>
     */
    private function calculatePageRank(array $linkGraph): array
    {
        $nodeCount = count($linkGraph);
        $linkGraphNodeNames = array_keys($linkGraph);
        // Initialise all PageRank as 1/n.
        $pageRank = array_fill_keys($linkGraphNodeNames, 1 / $nodeCount);
        $tempRank = array_fill_keys($linkGraphNodeNames, 0);

        $change = 1;
        $i = 0;
        while ($change > 0.00005 && $i < 100) {
            $change = 0;
            ++$i;

            // Distribute the PageRank of each page.
            foreach ($linkGraph as $node => $outbound) {
                $outboundCount = count($outbound);
                foreach ($outbound as $link) {
                    $tempRank += [$link => 0]; // Init temp rank for current link if not already set.
                    $tempRank[$link] += $pageRank[$node] / $outboundCount; // Update its value.
                }
            }

            $total = 0;
            $dampingFactor = 0.15;
            // Calculate the new PageRank using the damping factor.
            foreach ($linkGraph as $node => $outbound) {
                $tempRank[$node] = ($dampingFactor / $nodeCount) + (1 - $dampingFactor) * $tempRank[$node];
                $change += abs($pageRank[$node] - $tempRank[$node]);
                $pageRank[$node] = $tempRank[$node];
                $tempRank[$node] = 0;
                $total += $pageRank[$node];
            }

            // Normalise the page ranks, so it's all a proportion 0-1
            foreach ($pageRank as &$score) {
                $score /= $total;
            } unset($score);
        }

        return $pageRank;
    }
}
