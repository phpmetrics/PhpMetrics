<?php

namespace Test\Hal\Search;

use Hal\Metric\ClassMetric;
use Hal\Metric\InterfaceMetric;
use Hal\Metric\Metric;
use Hal\Search\Search;
use PHPUnit\Framework\TestCase;

/**
 * @group search
 */
class SearchTest extends TestCase
{

    public function testSearchCanReduceSearchByName()
    {
        $config = [
            'nameMatches' => 'awesome'
        ];

        $metric = $this->getMockBuilder(Metric::class)->getMock();
        $metric
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('My\\AwesomeClass'));

        $search = new Search('my-search', $config);

        $this->assertTrue($search->matches($metric));
    }

    public function testSearchCanReduceSearchByType()
    {
        $config = [
            'type' => 'class'
        ];

        $classMetric = $this->getMockBuilder(ClassMetric::class)->disableOriginalConstructor()->getMock();
        $interfaceMetric = $this->getMockBuilder(InterfaceMetric::class)->disableOriginalConstructor()->getMock();

        $search = new Search('my-search', $config);

        $this->assertTrue($search->matches($classMetric));
        $this->assertFalse($search->matches($interfaceMetric));
    }

    /**
     * @dataProvider providesMetrics
     */
    public function testSearchCanReduceSearchByMetric($searchExpression, $value, $expected)
    {
        $config = [
            'ccn' => $searchExpression
        ];

        $metric = $this->getMockBuilder(Metric::class)->getMock();
        $metric
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue($value));

        $search = new Search('my-search', $config);

        $this->assertEquals($expected, $search->matches($metric));
    }

    public function providesMetrics()
    {
        return [
            ['>=2.5', 6, true],
            ['>=2.5', 2.5, true],
            ['>=2.5', 2.4, false],
            ['>=2.5', 1, false],
            ['>=2.5', .3, false],
            ['<3', 2, true],
            ['<3', 3, false],
            ['<=3', 3, true],
            ['=3', 3, true],
            ['3', 3, true],
            ['=2', 3, false],
        ];
    }
}
