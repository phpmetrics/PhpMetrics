<?php
namespace Test\Hal\Application;

use Hal\Application\Application;
use Hal\Component\Output\TestOutput;
use Polyfill\TestCaseCompatible;

/**
 * Focused on the warning displayed when a custom exclusion list lets files
 * located in a "vendor" directory enter the analysis.
 *
 * @group application
 * @group config
 */
class ApplicationVendorWarningTest extends \PHPUnit\Framework\TestCase
{
    use TestCaseCompatible;

    private function warningFor(array $files)
    {
        $files = str_replace('/', DIRECTORY_SEPARATOR, $files);

        $output = new TestOutput();
        (new Application())->warnIfVendorIsAnalyzed($files, $output);

        return (string) $output->output;
    }

    public function testWarnsWhenVendorFilesAreAnalyzed(): void
    {
        $warning = $this->warningFor(['/app/src/Foo.php', '/app/vendor/acme/lib/Bar.php']);
        $this->assertStringContainsString('<warning>[!] ', $warning);
        $this->assertStringContainsString('vendor', $warning);
    }

    public function testWarnsWhenVendorIsNestedInAnalyzedDirectories(): void
    {
        $warning = $this->warningFor(['/app/src/module/vendor/Bar.php']);
        $this->assertStringContainsString('<warning>', $warning);
    }

    public function testStaysQuietWhenNoVendorFileIsAnalyzed(): void
    {
        $this->assertSame('', $this->warningFor(['/app/src/Foo.php', '/app/lib/Bar.php']));
    }

    public function testStaysQuietOnVendorLookalikeDirectories(): void
    {
        $this->assertSame('', $this->warningFor(['/app/vendor-tools/Foo.php', '/app/myvendor/Bar.php']));
    }
}
