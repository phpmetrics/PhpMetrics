<?php
namespace Test\Hal\Application\Config;

use Hal\Application\Config\Parser;

/**
 * @group application
 * @group config
 */
class ParserTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider providesExample
     *
     * @param string[] $argv
     * @param array<string,mixed> $expected
     */
    public function testICanParseArguments($argv, $expected)
    {
        $parser = new Parser();
        $config = $parser->parse($argv);
        $this->assertEquals($expected, $config->all());
    }

    /** @return array<mixed> */
    public function providesExample()
    {
        return [
            [
                ['--report-html=./build'],
                ['report-html' => './build']
            ],
            [
                ['--report-html ./build'],
                []
            ],
            [
                ['--argument'],
                ['argument' => true]
            ],
            [
                ['--argument1', '--argument2=abc'],
                ['argument1' => true, 'argument2' => 'abc']
            ],
            [
                [],
                []
            ],
            [
                ['--argument', 'myFolder'],
                ['argument' => true, 'files' => ['myFolder']]
            ],
            [
                ['--exclude=""', 'myFolder'],
                ['exclude' => "", 'files' => ['myFolder']]
            ]
        ];
    }
}
