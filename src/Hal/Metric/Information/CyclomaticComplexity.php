<?php
namespace Hal\Metric\Information;

class CyclomaticComplexity extends AbstractInformation
{
    const ID = 'ccn';
    protected $name = 'Cyclomatic Complexity';
    protected $shortDescription = 'Number of linearly independent paths';
    protected $links = ['http://en.wikipedia.org/wiki/Cyclomatic_complexity'];
}
