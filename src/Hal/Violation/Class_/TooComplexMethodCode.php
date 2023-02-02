<?php
declare(strict_types=1);

namespace Hal\Violation\Class_;

use Hal\Metric\ClassMetric;
use Hal\Metric\Metric;
use Hal\Violation\Violation;
use Hal\Violation\ViolationsHandlerInterface;

/**
 * According to McCabe,
 *
 *  The particular upper bound that has been used for cyclomatic complexity is 10
 *  which seems like a reasonable, but not magical, upper limit.
 *
 * @see http://www.literateprogramming.com/mccabe.pdf
 */
final class TooComplexMethodCode implements Violation
{
    private Metric $metric;

    public function getName(): string
    {
        return 'Too complex method code';
    }

    public function apply(Metric $metric): void
    {
        if (! $metric instanceof ClassMetric) {
            return;
        }

        $this->metric = $metric;

        if ($metric->get('ccnMethodMax') > 10) {
            /** @var ViolationsHandlerInterface $violationsHandler */
            $violationsHandler = $metric->get('violations');
            $violationsHandler->add($this);
        }
    }

    public function getLevel(): int
    {
        return Violation::ERROR;
    }

    public function getDescription(): string
    {
        /** @var int $ccnMethodMax */
        $ccnMethodMax = $this->metric->get('ccnMethodMax');

        return <<<EOT
This class looks really complex.

* Algorithms are complex (Max cyclomatic complexity of class methods is $ccnMethodMax)

Maybe you should delegate some code to other objects or split complex method.
EOT;
    }
}
