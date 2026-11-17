<?php

namespace Test\Hal\Violation\Class_;

use Hal\Metric\ClassMetric;
use Hal\Violation\Class_\Blob;
use Hal\Violation\Class_\ProbablyBugged;
use Hal\Violation\Class_\TooDependent;
use Hal\Violation\Class_\TooLong;
use Hal\Violation\Violations;
use PHPUnit\Framework\Attributes\DataProvider;

class MetricPropertyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider provideViolations
     */
    #[DataProvider('provideViolations')]
    public function testMetricCanBeStoredAndUsedWithoutDeprecations($violationClass): void
    {
        $metric = new ClassMetric('Example');
        $metric->set('violations', new Violations());
        $metric->set('nbMethodsPublic', 9);
        $metric->set('lcom', 3);
        $metric->set('externals', array_fill(0, 10, 'Dependency'));
        $metric->set('bugs', 1);
        $metric->set('numberOfUnitTests', 0);
        $metric->set('lloc', 200);
        $metric->set('loc', 250);
        $metric->set('efferentCoupling', 20);
        $violation = new $violationClass();

        set_error_handler(function ($severity, $message, $file, $line) {
            throw new \ErrorException($message, 0, $severity, $file, $line);
        }, E_DEPRECATED);
        try {
            $violation->apply($metric);
            $this->assertSame(1, $metric->get('violations')->count());
            $this->assertNotEmpty($violation->getDescription());
        } finally {
            restore_error_handler();
        }
    }

    public static function provideViolations()
    {
        return [
            'blob' => [Blob::class],
            'probably bugged' => [ProbablyBugged::class],
            'too dependent' => [TooDependent::class],
            'too long' => [TooLong::class],
        ];
    }
}
