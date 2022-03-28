<?php
declare(strict_types=1);

namespace Tests\Hal\Metric;

use Hal\Metric\Registry;
use PHPUnit\Framework\TestCase;

final class RegistryTest extends TestCase
{
    public function testRegistryMetricsCodes(): void
    {
        $expected = [
            'name',
            'length',
            'vocabulary',
            'volume',
            'difficulty',
            'effort',
            'level',
            'bugs',
            'time',
            'intelligentContent',
            'number_operators',
            'number_operands',
            'number_operators_unique',
            'number_operands_unique',
            'ccn',
            'ccnMethodMax',
            'kanDefect',
            'mi',
            'mIwoC',
            'commentWeight',
            'externals',
            'parents',
            'lcom',
            'relativeStructuralComplexity',
            'relativeDataComplexity',
            'relativeSystemComplexity',
            'cloc',
            'loc',
            'lloc',
            'methods',
            'nbMethodsIncludingGettersSetters',
            'nbMethods',
            'nbMethodsPrivate',
            'nbMethodsPublic',
            'nbMethodsGetters',
            'nbMethodsSetters',
            'afferentCoupling',
            'efferentCoupling',
            'instability',
            'depthOfInheritanceTree',
            'pageRank',
        ];

        self::assertSame($expected, Registry::allForStructures());
    }

    public function testRegistryContent(): void
    {
        $expected = [
            'name' => 'Name of component',
            'length' => "Halstead's program length",
            'vocabulary' => "Halstead's vocabulary",
            'volume' => "Halstead's program volume",
            'difficulty' => "Halstead's difficulty",
            'effort' => "Halstead's effort",
            'level' => "Halstead's program level",
            'bugs' => "Halstead's estimation of number of bugs",
            'time' => "Halstead's estimated time to program",
            'intelligentContent' => "Halstead's program level",
            'number_operators' => 'Number of operators',
            'number_operands' => 'Number of operands',
            'number_operators_unique' => 'Number of unique operators',
            'number_operands_unique' => 'Number of unique operands',
            'ccn' => 'Cyclomatic complexity',
            'ccnMethodMax' => 'Max Cyclomatic complexity for method',
            'kanDefect' => "Kan's defects",
            'mi' => 'Maintainability Index',
            'mIwoC' => 'Maintainability Index without comments',
            'commentWeight' => 'Weight of comments',
            'externals' => 'List of external dependencies',
            'parents' => 'List of parent classes',
            'lcom' => 'Lack of cohesion of methods',
            'relativeStructuralComplexity' => 'Relative structural complexity',
            'relativeDataComplexity' => 'Relative data complexity',
            'relativeSystemComplexity' => 'Relative system complexity',
            'cloc' => 'Comment Lines of Code',
            'loc' => 'Lines of Code',
            'lloc' => 'Logical Lines of Code',
            'methods' => 'List of methods',
            'nbMethodsIncludingGettersSetters' => 'Number of methods including getters and setters',
            'nbMethods' => 'Number of methods excluding getters and setters',
            'nbMethodsPrivate' => 'Number of private methods',
            'nbMethodsPublic' => 'Number of public methods',
            'nbMethodsGetter' => 'Number of getters',
            'nbMethodsSetters' => 'Number of setters',
            'afferentCoupling' => 'Afferent coupling',
            'efferentCoupling' => 'Efferent coupling',
            'instability' => 'Package Instability',
            'depthOfInheritanceTree' => 'Depth of inheritance tree',
            'pageRank' => 'PageRank for component',
        ];

        self::assertSame($expected, Registry::getDefinitions());
    }
}
