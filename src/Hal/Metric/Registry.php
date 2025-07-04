<?php

namespace Hal\Metric;

class Registry
{
    private $definitionsForStructures = [
        'name' => 'Name of component',
        'length' => "Halstead'e program length",
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
        'ccn' => "Cyclomatic complexity",
        'ccnMethodMax' => 'Max Cyclomatic complexity for method',
        'kanDefect' => "Kan's defects",
        'mi' => "Maintainability Index",
        'mIwoC' => "Maintainability Index without comments",
        'commentWeight' => "Weight of comments",
        'externals' => "List of external dependencies",
        'parents' => "List of parent classes",
        'lcom' => "Lack of cohesion of methods",
        'relativeStructuralComplexity' => "Relative structural complexity",
        'relativeDataComplexity' => "Relative data complexity",
        'relativeSystemComplexity' => "Relative system complexity",
        'totalStructuralComplexity' => "Total structural complexity",
        'totalDataComplexity' => "Total data complexity",
        'totalSystemComplexity' => "Total system complexity",
        'cloc' => "Comment Lines of Code",
        'loc' => "Lines of Code",
        'lloc' => "Logical Lines of Code",
        'methods' => "List of methods",
        'nbMethodsIncludingGettersSetters' => "Number of methods including getters and setters",
        'nbMethods' => "Number of methods excluding getters and setters",
        'nbMethodsPrivate' => "Number of private methods",
        'nbMethodsPublic' => "Number of public methods",
        'nbMethodsGetter' => "Number of getters",
        'nbMethodsSetters' => "Number of setters",
        'afferentCoupling' => "Afferent coupling",
        'efferentCoupling' => "Efferent coupling",
        'instability' => "Package Instability",
        'depthOfInheritanceTree' => "Depth of inheritance tree",
        'pageRank' => "PageRank for component",
    ];

    public function allForProject() {}

    public function allForStructures()
    {
        return array_keys($this->definitionsForStructures);
    }

    public function getDefinitions()
    {
        return $this->definitionsForStructures;
    }
}
