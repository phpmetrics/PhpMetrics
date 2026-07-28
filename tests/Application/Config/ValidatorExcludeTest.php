<?php
namespace Test\Hal\Application\Config;

use Hal\Application\Config\Config;
use Hal\Application\Config\Validator;
use Polyfill\TestCaseCompatible;

/**
 * Focused on the "exclude" option (default exclusion list vs. custom one).
 *
 * @group application
 * @group config
 */
class ValidatorExcludeTest extends \PHPUnit\Framework\TestCase
{
    use TestCaseCompatible;

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

    public function testDefaultExclusionListIsAppliedWhenNotProvided(): void
    {
        $config = $this->validated([]);
        $this->assertSame(explode(',', Validator::DEFAULT_EXCLUDE), $config->get('exclude'));
    }

    public function testCustomExclusionListReplacesDefaultInsteadOfExtendingIt(): void
    {
        $config = $this->validated(['exclude' => 'foo,bar']);
        $this->assertSame(['foo', 'bar'], $config->get('exclude'));
    }

    public function testHelpWarnsAboutExclusionListReplacement(): void
    {
        $help = (new Validator())->help();
        $this->assertStringContainsString('replaces the default exclusion list', $help);
        $this->assertStringContainsString(Validator::DEFAULT_EXCLUDE, $help);
    }
}
