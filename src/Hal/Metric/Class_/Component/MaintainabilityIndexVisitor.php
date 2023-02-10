<?php
declare(strict_types=1);

namespace Hal\Metric\Class_\Component;

use Hal\Metric\Class_\Complexity\CyclomaticComplexityVisitor;
use Hal\Metric\Class_\Text\HalsteadVisitor;
use Hal\Metric\Class_\Text\LengthVisitor;
use Hal\Metric\Helper\MetricNameGenerator;
use Hal\Metric\Metric;
use Hal\Metric\Metrics;
use LogicException;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;
use function is_infinite;
use function log;
use function max;
use function round;
use function sin;
use function sqrt;

/**
 * Calculates Maintainability Index
 *
 * According to Wikipedia, "Maintainability Index is a software metric which measures how maintainable (easy to
 * support and change) the source code is. The maintainability index is calculated as a factored formula consisting
 * of Lines Of Code, Cyclomatic Complexity and Halstead volume."
 *
 * MIwoc: Maintainability Index without comments
 * MIcw: Maintainability Index comment weight
 * MI: Maintainability Index = MIwoc + MIcw
 *
 * MIwoc = 171 - 5.2 * log2({HalsteadVolume}) - 0.23 * {ClassCyclomaticComplexity} - 16.2 * log2({LogicalLinesOfCode})
 * MIcw = 50 * sin(sqrt(2.4 * {CommentedLinesOfCode} / {LinesOfCode})). If {LinesOfCode} = 0, MIcw = 0.
 * MI = MIwoc + MIcw
 *
 * To calculate these metrics, the following visitors are required to be executed first:
 * - @uses LengthVisitor for lloc, cloc and loc.
 * - @uses CyclomaticComplexityVisitor for ccn.
 * - @uses HalsteadVisitor for volume.
 */
final class MaintainabilityIndexVisitor extends NodeVisitorAbstract
{
    /**
     * @param Metrics $metrics
     */
    public function __construct(
        private readonly Metrics $metrics
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function leaveNode(Node $node): null|int|Node|array // TODO PHP 8.2: only return null here.
    {
        if (
            !$node instanceof Stmt\Class_
            && !$node instanceof Stmt\Trait_
            //TODO: && !$node instanceof Stmt\Enum_
        ) {
            return null;
        }

        /** @var Metric $class */
        $class = $this->metrics->get(MetricNameGenerator::getClassName($node));
        /** @var null|float $lloc */
        $lloc = $class->get('lloc');
        /** @var null|float $cloc */
        $cloc = $class->get('cloc');
        /** @var null|float $loc */
        $loc = $class->get('loc');
        /** @var null|float $ccn */
        $ccn = $class->get('ccn');
        /** @var null|float $volume */
        $volume = $class->get('volume');

        if (null === $lloc || null === $cloc || null === $loc) {
            throw new LogicException('Please enable ' . LengthVisitor::class . ' visitor first');
        }
        if (null === $ccn) {
            throw new LogicException('Please enable ' . CyclomaticComplexityVisitor::class . ' visitor first');
        }
        if (null === $volume) {
            throw new LogicException('Please enable ' . HalsteadVisitor::class . ' visitor first');
        }

        // Maintainability index without comment.
        $miWithoutComments = max((171 - (5.2 * log($volume)) - (0.23 * $ccn) - (16.2 * log($lloc))) * 100 / 171, 0);
        if (is_infinite($miWithoutComments)) {
            $miWithoutComments = 171;
        }

        // Comment weight
        $commentWeight = ($loc > 0) ? 50 * sin(sqrt(2.4 * $cloc / $loc)) : 0;

        // Maintainability index
        $mi = $miWithoutComments + $commentWeight;

        $class->set('mi', round($mi, 2));
        $class->set('mIwoC', round($miWithoutComments, 2));
        $class->set('commentWeight', round($commentWeight, 2));
        $this->metrics->attach($class);

        return null;
    }
}
