<?php
namespace Hal\Metric\Information;

class LogicalLinesOfCode extends AbstractInformation
{
    const ID = 'lloc';
    protected $name = 'Logical Lines of Code';
    protected $shortDescription = 'Lines of code excluding blank lines and comments';
    protected $longDescription = '<p>Definitions vary for what a line of code actually is. We use PhpParser\PrettyPrinter to reformat the code and count the resulting lines.</p>';
    protected $links = ['https://en.wikipedia.org/wiki/Source_lines_of_code'];
}
