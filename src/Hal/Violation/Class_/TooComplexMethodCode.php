<?php
namespace Hal\Violation\Class_;


use Hal\Metric\ClassMetric;
use Hal\Metric\Metric;
use Hal\Violation\Violation;

class TooComplexMethodCode implements Violation
{

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Too complex method code';
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

        if ($metric->get('ccnMethodMax') >= 8) {
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

* Algorithms are complex (Max cyclomatic complexity of class methods is {$this->metric->get('ccnMethodMax')})

Maybe you should delegate some code to other objects or split complex method.
EOT;

    }
}
