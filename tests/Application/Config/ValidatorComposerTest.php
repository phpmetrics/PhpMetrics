<?php
namespace Test\Hal\Application\Config;

use Hal\Application\Config\Config;
use Hal\Application\Config\Validator;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Focused on the dual-purpose "composer" option (boolean toggle vs. explicit path).
 *
 * @group application
 * @group config
 * @group composer
 */
class ValidatorComposerTest extends \PHPUnit\Framework\TestCase
{
    private function validated(array $extra)
    {
        $config = new Config();
        // A valid, existing directory is required by the validator.
        $config->set('files', [__DIR__]);
        $config->fromArray($extra);

        $validator = new Validator();
        $validator->validate($config);

        return $config;
    }

    public function testComposerDefaultsToEnabled(): void
    {
        $config = $this->validated([]);
        $this->assertTrue($config->get('composer'));
    }

    /**
     * @dataProvider provideBooleanValues
     */
    #[DataProvider('provideBooleanValues')]
    public function testComposerBooleanKeywordsAreCoercedToBool($value, $expected): void
    {
        $config = $this->validated(['composer' => $value]);
        $this->assertSame($expected, $config->get('composer'));
    }

    public static function provideBooleanValues()
    {
        return [
            [true, true],
            [false, false],
            ['true', true],
            ['false', false],
            ['1', true],
            ['0', false],
            ['yes', true],
            ['no', false],
            ['on', true],
            ['off', false],
            ['', false],
        ];
    }

    public function testComposerPathIsKeptAsString(): void
    {
        $path = './some/nested/composer.json';
        $config = $this->validated(['composer' => $path]);
        $this->assertSame($path, $config->get('composer'));
    }

    public function testComposerPathIsTrimmed(): void
    {
        $config = $this->validated(['composer' => '  ./composer.json  ']);
        $this->assertSame('./composer.json', $config->get('composer'));
    }
}
