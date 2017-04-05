<?php
namespace Test\Hal\Metric\System\UnitTesting;

use Hal\Application\Config\Config;
use Hal\Metric\Metrics;
use Hal\Metric\System\UnitTesting\UnitTesting;

/**
 * @group unit
 */
class UnitTestingTest extends \PHPUnit_Framework_TestCase
{

    public function testICanParseJunitXmlFile() {

        $config = new Config();
        $config->set('junit', __DIR__ . '/xml/junit1.xml');
        $unit = new UnitTesting($config, []);
        $metrics = new Metrics();
        $unit->calculate($metrics);

        $this->assertEquals(2, $metrics->get('unitTesting')->get('nbSuites'));
        $this->assertCount(2, $metrics->get('unitTesting')->get('tests'));

        $tests = $metrics->get('unitTesting')->get('tests');
        $this->assertArrayHasKey('Test\Hal\Application\Config\ParserTest', $tests);
        $this->assertEquals(7, $tests['Test\Hal\Application\Config\ParserTest']->assertions);

        $tests = $metrics->get('unitTesting')->get('tests');
        $this->assertArrayHasKey('Test\Hal\Component\Issue\IssuerTest', $tests);
        $this->assertEquals(6, $tests['Test\Hal\Component\Issue\IssuerTest']->assertions);
    }

    /**
     * @expectedException Hal\Application\Config\ConfigException
     */
    public function testExceptionIsThrownIfJunitFileDoesNotExist()
    {
        $config = new Config();
        $config->set('junit', __DIR__ . '/xml/junit-not-found.xml');
        $unit = new UnitTesting($config, []);
        $metrics = new Metrics();
        $unit->calculate($metrics);
    }

    public function testICanParseCodeceptionFile() {

        $config = new Config();
        $config->set('junit', __DIR__ . '/xml/codeception1.xml');
        $unit = new UnitTesting($config, []);
        $metrics = new Metrics();
        $unit->calculate($metrics);

        $this->assertEquals(3, $metrics->get('unitTesting')->get('nbSuites'));
        $this->assertEquals(27, $metrics->get('unitTesting')->get('assertions'));
    }
}