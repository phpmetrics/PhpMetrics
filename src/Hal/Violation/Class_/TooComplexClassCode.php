<?php
declare(strict_types=1);

namespace Hal\Violation\Class_;

use Hal\Metric\ClassMetric;
use Hal\Metric\Metric;
use Hal\Violation\Violation;
use Hal\Violation\ViolationsHandlerInterface;

/**
 * 50 as a threshold seems to be widely accepted in open source metric tools.
 *
 * @see https://github.com/phpmd/phpmd/blob/master/src/main/resources/rulesets/codesize.xml#L390
 */
final class TooComplexClassCode implements Violation
{
    private Metric $metric;

    public function getName(): string
    {
        return 'Too complex class code';
    }

    public function apply(Metric $metric): void
    {
        if (! $metric instanceof ClassMetric) {
            return;
        }

        $this->metric = $metric;

        if ($metric->get('wmc') > 50) {
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
        /** @var int $ccn */
        $ccn = $this->metric->get('ccn');
        /** @var int $nbOperators */
        $nbOperators = $this->metric->get('number_operators');

        return <<<EOT
This class looks really complex.

* Algorithms are complex (Total cyclomatic complexity of class is $ccn)
* Component uses $nbOperators operators

Maybe you should delegate some code to other objects.
EOT;
    }
}
