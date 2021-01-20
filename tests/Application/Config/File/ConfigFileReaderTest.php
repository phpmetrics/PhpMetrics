<?php
namespace Test\Hal\Application\Config\File;

use Hal\Application\Config\File\ConfigFileReaderInterface;

/**
 * @group config
 */
class ConfigFileReaderTest extends \PHPUnit\Framework\TestCase
{
    public function testJsonConfigFile()
    {
        $filename = __DIR__ . '/examples/config.json';

        $config = new \Hal\Application\Config\Config();

        /** @var ConfigFileReaderInterface $reader */
        $reader = \Hal\Application\Config\File\ConfigFileReaderFactory::createFromFileName($filename);
        $reader->read($config);

        $this->assertEquals($this->getExpectedData(), $config->all());
    }

    /**
     *
     */
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
                    "match"=> "!app!i"
                ],
                [
                    "name" => "Models",
                    "match" => "!app!i"
                ]
            ]
        ];
    }
}
