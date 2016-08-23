<?php
namespace Hal\Metric\Information;

class MaintainabilityIndex extends AbstractInformation
{
    const ID = 'mi';
    protected $name = 'Maintainability Index';
    protected $shortDescription = 'How maintainable is this code?';
    protected $links = ['https://en.wikipedia.org/wiki/Maintainability'];
    protected $formula = MaintainabilityIndexWithoutComments::ID . ' + ' . CommentWeight::ID;
}
