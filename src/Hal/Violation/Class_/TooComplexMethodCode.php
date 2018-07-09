<?php
namespace Hal\Violation\Class_;

use Hal\Metric\ClassMetric;
use Hal\Metric\Metric;
use Hal\Violation\Violation;

/**
 * According to McCabe,
 *
 *  The particular upper bound that has been used for cyclomatic complexity is 10
 *  which seems like a reasonable, but not magical, upper limit.
 *
 * @see http://www.literateprogramming.com/mccabe.pdf
 */
class TooComplexMethodCode implements Violation
{
    /** @var Metric|null */
    private $metric;

    public function getName()
    {
        return 'Too complex method code';
    }

    public function apply(Metric $metric)
    {
        if (! $metric instanceof ClassMetric) {
            return;
        }

        $this->metric = $metric;

        if ($metric->get('ccnMethodMax') > 10) {
            $metric->get('violations')->add($this);
            return;
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

* Algorithms are complex (Max cyclomatic complexity of class methods is {$this->metric->get('ccnMethodMax')})

Maybe you should delegate some code to other objects or split complex method.
EOT;
    }
}
