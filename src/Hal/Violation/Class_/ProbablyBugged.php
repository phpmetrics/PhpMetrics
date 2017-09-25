<?php
namespace Hal\Violation\Class_;

use Hal\Metric\ClassMetric;
use Hal\Metric\Metric;
use Hal\Violation\Violation;

/**
 * Class ProbablyBugged
 *
 * @package Hal\Violation\Class_
 */
class ProbablyBugged implements Violation
{

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Probably bugged';
    }

    /**
     * @inheritdoc
     */
    public function apply(Metric $metric)
    {
        if (!$metric instanceof ClassMetric) {
            return;
        }

        $this->metric = $metric;

        if ($metric->get('bugs') >= .35) {
            $metric->get('violations')->add($this);
            return;
        }
    }

    /**
     * @inheritdoc
     */
    public function getLevel()
    {
        return Violation::WARNING;
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return <<<EOT
This component contains in theory {$this->metric->get('bugs')} bugs.

* Calculation is based on number of operators, operands, cyclomatic complexity
* See more details at https://en.wikipedia.org/wiki/Halstead_complexity_measures
* {$this->metric->get('numberOfUnitTests')} testsuites has dependency to this class.

Maybe you should check your unit tests for this class.
EOT;
    }
}
