<?php
namespace Hal\Metric;

class Registry
{
    public function allForProject()
    {

    }

    public function allForStructures()
    {
        return [
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
            'totalStructuralComplexity',
            'totalDataComplexity',
            'totalSystemComplexity',
            'cloc',
            'loc',
            'lloc',
            'methods',
            'nbMethodsIncludingGettersSetters',
            'nbMethods',
            'nbMethodsPrivate',
            'nbMethodsPublic',
            'nbMethodsGetter',
            'nbMethodsSetters',
            'afferentCoupling',
            'efferentCoupling',
            'instability',
            'depthOfInheritanceTree',
            'pageRank',

        ];
    }
}
