<?php
declare(strict_types=1);

namespace Hal\Metric;

use function array_keys;

/**
 * List of all metrics.
 */
final class Registry
{
    /** @var array<string, string> List of metrics definitions. */
    private static array $definitionsForStructures = [
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
        'nbMethods' => 'Number of methods',
        'nbMethodsPrivate' => 'Number of private methods',
        'nbMethodsPublic' => 'Number of public methods',
        'afferentCoupling' => 'Afferent coupling',
        'efferentCoupling' => 'Efferent coupling',
        'instability' => 'Package Instability',
        'depthOfInheritanceTree' => 'Depth of inheritance tree',
    ];

    /**
     * Returns the list of metrics code names.
     *
     * @return array<int, string>
     */
    public static function allForStructures(): array
    {
        return array_keys(self::$definitionsForStructures);
    }

    /**
     * Returns the list of metrics definitions.
     *
     * @return array<string, string>
     */
    public static function getDefinitions(): array
    {
        return self::$definitionsForStructures;
    }
}
