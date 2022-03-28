<?php
declare(strict_types=1);

namespace Tests\Hal\Component\File;

use Hal\Component\File\Finder;
use PHPUnit\Framework\TestCase;
use function dirname;
use function realpath;

final class FinderTest extends TestCase
{
    public function testFinderCanFetchFilesRegardingConfiguration(): void
    {
        $resourceFilePath = realpath(dirname(__DIR__, 2)) . '/resources/component/file';

        $finder = new Finder(['php', 'inc'], ['excluded', '.excluded']);
        $fetchedFiles = $finder->fetch([$resourceFilePath, $resourceFilePath . '/directIncludedFile.txt']);
        $expected = [
            $resourceFilePath . '/included/includedFile.php',
            $resourceFilePath . '/includedFile.php.inc',
            $resourceFilePath . '/directIncludedFile.txt',
        ];

        self::assertSame($expected, $fetchedFiles);
    }
}
