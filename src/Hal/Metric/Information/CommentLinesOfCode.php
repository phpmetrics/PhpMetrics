<?php
namespace Hal\Metric\Information;

class CommentLinesOfCode extends AbstractInformation
{
    const ID = 'cloc';
    protected $name = 'Comment Lines';
    protected $shortDescription = 'Total number of lines which are comments';
}
