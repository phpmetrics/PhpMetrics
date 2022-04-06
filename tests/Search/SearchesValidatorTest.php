<?php
declare(strict_types=1);

namespace Tests\Hal\Search;

use Generator;
use Hal\Exception\ConfigException\SearchValidationException;
use Hal\Metric\Registry;
use Hal\Search\Search;
use Hal\Search\SearchesValidator;
use Hal\Search\SearchInterface;
use Phake;
use Phake\IMock;
use PHPUnit\Framework\TestCase;
use function array_fill_keys;
use function array_map;

final class SearchesValidatorTest extends TestCase
{
    public function testICanValidateSearchConfigurations(): void
    {
        $configValues = [
            'type' => 'class',
            'nameMatches' => 'a|b$',
            'instanceOf' => ['\\A', 'B'],
            'usesClasses' => ['^A|B$', '^C', 'D$'],
            'failIfFound' => true,
            ...array_fill_keys(Registry::allForStructures(), '>0')
        ];

        $listSearchConfigurations = [];
        foreach ($configValues as $configKey => $configValue) {
            $rawSearchConfig = [$configKey => $configValue];
            $search = Phake::mock(SearchInterface::class);
            Phake::when($search)->__call('getConfig', [])->thenReturn($rawSearchConfig);
            $listSearchConfigurations[$configKey . 'Test'] = $search;
        }
        (new SearchesValidator())->validates($listSearchConfigurations);

        array_map(static function (IMock&SearchInterface $search): void {
            Phake::verify($search)->__call('getConfig', []);
            Phake::verifyNoOtherInteractions($search);
        }, $listSearchConfigurations);
    }

    public function testICantValidateSearchConfigurationsWhenUsingUnknownKey(): void
    {
        $rawSearches = [
            'unknownKey' => ['thisIsAnUnknownKey' => ' = 0'],
        ];
        $allowedKeys = [
            'type',
            'nameMatches',
            'instanceOf',
            'usesClasses',
            'failIfFound',
            ...Registry::allForStructures()
        ];

        $this->expectExceptionObject(SearchValidationException::unknownSearchKey('unknownKey', $allowedKeys));

        (new SearchesValidator())->validates(Search::buildListFromArray($rawSearches));
    }

    /**
     * @return Generator<string, array{array<SearchInterface>, SearchValidationException}>
     */
    public function provideInvalidSearchesConfigurations(): Generator
    {
        $searches = Search::buildListFromArray(['test' => ['type' => 'unknownType']]);
        $exception = SearchValidationException::invalidType();
        yield 'type must be "class" or "interface"' => [$searches, $exception];

        $searches = Search::buildListFromArray(['test' => ['nameMatches' => [42]]]);
        $exception = SearchValidationException::invalidNameMatches();
        yield 'nameMatches must be a string' => [$searches, $exception];

        $searches = Search::buildListFromArray(['test' => ['instanceOf' => '\\A']]);
        $exception = SearchValidationException::invalidInstanceOf();
        yield 'instanceOf must be an array' => [$searches, $exception];

        $searches = Search::buildListFromArray(['test' => ['usesClasses' => '\\A']]);
        $exception = SearchValidationException::invalidUsesClasses();
        yield 'usesClasses must be an array' => [$searches, $exception];
    }

    /**
     * @dataProvider provideInvalidSearchesConfigurations
     * @param array<SearchInterface> $searches
     * @param SearchValidationException $exception
     * @return void
     */
    //#[DataProvider('provideInvalidSearchesConfigurations')] TODO: PHPUnit 10.
    public function testICantValidateSearchConfigurationsWithInvalidSpecialSearches(
        array $searches,
        SearchValidationException $exception
    ): void {
        $this->expectExceptionObject($exception);
        (new SearchesValidator())->validates($searches);
    }
}
