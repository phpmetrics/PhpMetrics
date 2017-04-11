<?php
namespace Hal\Violation\Class_;


use Hal\Metric\ClassMetric;
use Hal\Metric\Metric;
use Hal\Violation\Violation;

class TooComplexClassCode implements Violation
{

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Too complex class code';
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

        if ($metric->get('ccn') >= 25) {
            $metric->get('violations')->add($this);
            return;
        }

    }

    /**
     * @inheritdoc
     */
    public function getLevel()
    {
        return Violation::ERROR;
    }

    /**
     * @inheritdoc
     */
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
