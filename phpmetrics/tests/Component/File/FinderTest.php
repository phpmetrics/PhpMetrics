<?php

namespace Test\Hal\Component\File;

use Hal\Component\File\Finder;
use PHPUnit\Framework\TestCase;

/**
 * @group file
 */
class FinderTest extends TestCase
{
    public function testPathsGivenAreRecoveredOverExcluded()
    {
        $exampleRoot = __DIR__ . DIRECTORY_SEPARATOR . 'examples';

        $extensions = ['php', 'inc', 'phtml'];
        $excludedDirs = [
            'excluded', //Simple string name
            '.excluded', //Name containing a special regex character (".")
        ];

        $inputFiles = [$exampleRoot];

        $finder = new Finder($extensions, $excludedDirs);
        $files = $finder->fetch($inputFiles);

        $expected = [
            $exampleRoot . DIRECTORY_SEPARATOR . 'included' . DIRECTORY_SEPARATOR . 'includedFile.php',
            $exampleRoot . DIRECTORY_SEPARATOR . 'includedFile.php.inc',
        ];

        //Sorting the expected and the actual array values to assert both array are same.
        \sort($expected);
        \sort($files);

        static::assertSame($expected, $files);
    }

    public function testGivenPathsAreIgnoredRegardingExclusion()
    {
        $exampleRoot = __DIR__ . DIRECTORY_SEPARATOR . 'examples';
        $actualFoundFiles = (new Finder(['php'], ['tests']))->fetch([$exampleRoot]);
        $expectedFoundFiles = [
            $exampleRoot . DIRECTORY_SEPARATOR . 'excluded' . DIRECTORY_SEPARATOR . 'excludedFile.php',
            $exampleRoot . DIRECTORY_SEPARATOR . 'included' . DIRECTORY_SEPARATOR . 'includedFile.php',
        ];
        sort($actualFoundFiles);
        sort($expectedFoundFiles);
        $this->assertSame($expectedFoundFiles, $actualFoundFiles);
    }
}
