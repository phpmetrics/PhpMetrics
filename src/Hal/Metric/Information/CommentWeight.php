<?php
namespace Hal\Metric\Information;

class CommentWeight extends AbstractInformation
{
    const ID = 'commentWeight';
    protected $name = 'Comment Weight';
    protected $shortDescription = 'Ratio between logical code and comments';
    protected $formula = '50 * sin(sqrt(2.4 * cloc/loc))'; // todo use IDs
}
