<?php
namespace Hal\Metric\Information;

class MaintainabilityIndexWithoutComments extends AbstractInformation
{
    const ID = 'mIwoC';
    protected $name = 'Maintainability Index Without Comments';
    protected $shortDescription = 'How maintainable is this code?';
    protected $longDescription = '<p>Maintainability index evaluates the maintainability of any project. It provides a score between 0 to 118.
This score is standard, and works for any language : PHP, .Net, Java... Value ranges are:</p>
<ul>
<li>&lt;64: low maintainability. The project has probably technical debt.</li>
<li>65-84: medium maintainability. The project has problems, but nothing really serious.</li>
<li>&gt;85: high maintainability. The project is probably good.</li>
</ul>';
    protected $links = ['https://en.wikipedia.org/wiki/Maintainability'];
    protected $formula = '171 - (5.2 * \log( volume )) - (0.23 * ' . CyclomaticComplexity::ID . ') - (16.2 * \log(' . LogicalLinesOfCode::ID . '))) * 100 / 171';
}
