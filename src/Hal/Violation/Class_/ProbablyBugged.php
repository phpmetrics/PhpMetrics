<?php
declare(strict_types=1);

namespace Hal\Violation\Class_;

use Hal\Metric\ClassMetric;
use Hal\Metric\Metric;
use Hal\Violation\Violation;
use Hal\Violation\ViolationsHandlerInterface;

/**
 * This class triggers a violation when the probability of bugs in a class according to Halstead metrics is too high.
 */
final class ProbablyBugged implements Violation
{
    private Metric $metric;

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'Probably bugged';
    }

    /**
     * @inheritdoc
     */
    public function apply(Metric $metric): void
    {
        if (!$metric instanceof ClassMetric) {
            return;
        }

        $this->metric = $metric;

        if ($metric->get('bugs') >= .35) {
            /** @var ViolationsHandlerInterface $violationsHandler */
            $violationsHandler = $metric->get('violations');
            $violationsHandler->add($this);
        }
    }

    /**
     * @inheritdoc
     */
    public function getLevel(): int
    {
        return Violation::WARNING;
    }

    /**
     * @inheritdoc
     */
    public function getDescription(): string
    {
        /** @var float $bugsProportion */
        $bugsProportion = $this->metric->get('bugs');

        return <<<EOT
This component contains in theory $bugsProportion bugs.

* Calculation is based on number of operators, operands, cyclomatic complexity
* See more details at https://en.wikipedia.org/wiki/Halstead_complexity_measures

Maybe you should check your unit tests for this class.
EOT;
    }
}
