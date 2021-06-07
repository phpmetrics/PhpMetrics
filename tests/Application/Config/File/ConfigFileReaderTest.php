<?php

namespace Test\Hal\Application\Config\File;

use Hal\Application\Config\Config;
use Hal\Application\Config\File\ConfigFileReaderFactory;
use Hal\Search\Searches;

/**
 * @group config
 */
class ConfigFileReaderTest extends \PHPUnit\Framework\TestCase
{
    public function testICanParseBasicJsonFIle()
    {
        $filename = __DIR__ . '/examples/config.json';

        $config = new Config();

        $reader = ConfigFileReaderFactory::createFromFileName($filename);
        $reader->read($config);

        $this->assertEquals($this->getExpectedData(), $config->all());
    }

    private function getExpectedData()
    {
        return [
            'exclude' => 'tests',
            'report-html' => __DIR__ . '/examples/tmp/report/',
            'report-json' => '/tmp/report.json',
            'report-csv' => '/tmp/report.csv',
            'report-violations' => '/tmp/violations.xml',
            'extensions' => 'php,php8',
            'git' => 'git',
            'junit' => '/tmp/junit.xml',
            'files' => [
                __DIR__ . '/examples/src/Hal/Component'
            ],
            'groups' => [
                [
                    "name" => "Controllers",
                    "match" => "!component!i"
                ],
                [
                    "name" => "Domain",
                    "match" => "!app!i"
                ],
                [
                    "name" => "Models",
                    "match" => "!app!i"
                ]
            ],
            'searches' => new Searches()
        ];
    }

    public function testICanParseConfigWithSearch()
    {
        $filename = __DIR__ . '/examples/config-with-search.json';

        $config = new Config();

        $reader = ConfigFileReaderFactory::createFromFileName($filename);
        $reader->read($config);

        $this->assertInstanceOf(Searches::class, $config->get('searches'));
        $searches = $config->get('searches');
        $this->assertEquals('my-search1', $searches->get('my-search1')->getName());
        $this->assertEquals('my-search2', $searches->get('my-search2')->getName());
        $this->assertFalse($searches->has('my-searchNOT_FOUND'));
    }
}
