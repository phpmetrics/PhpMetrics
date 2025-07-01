<?php

namespace Hal\Violation\Class_;

use Hal\Metric\ClassMetric;
use Hal\Metric\Metric;
use Hal\Violation\Violation;

/**
 * 50 as a threshold seems to be widely accepted in open source metric tools.
 *
 * @see http://staff.unak.is/andy/StaticAnalysis0809/metrics/wmc.html
 * @see https://github.com/phpmd/phpmd/blob/f1c145e538d7cf8c2d1a45fd8fb723eca64005f4/src/main/resources/rulesets/codesize.xml#L390
 */
class TooComplexClassCode implements Violation
{
    /** @var Metric|null */
    private $metric;

    public function getName()
    {
        return 'Too complex class code';
    }

    public function apply(Metric $metric)
    {
        if (! $metric instanceof ClassMetric) {
            return;
        }

        $this->metric = $metric;

        if ($metric->get('wmc') > 50) {
            $metric->get('violations')->add($this);
        }
    }

    public function getLevel()
    {
        return Violation::ERROR;
    }

    public function getDescription()
    {
        return <<<EOT
            This class looks really complex.

            * Algorithms are complex (Total cyclomatic complexity of class is {$this->metric->get('ccn')})
            * Component uses {$this->metric->get('number_operators')} operators

            Maybe you should delegate some code to other objects.
EOT;
    }
}
