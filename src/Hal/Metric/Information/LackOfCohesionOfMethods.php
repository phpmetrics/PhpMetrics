<?php
namespace Hal\Metric\Information;

class LackOfCohesionOfMethods extends AbstractInformation
{
    const ID = 'lcom';
    protected $name = 'Lack of cohesion of methods';
    protected $shortDescription = 'Do the methods in this class belong together?';
    protected $longDescription = '<p>Looking at the internal dependencies of this class, do the methods have a single responsibility and work together on the same data? Or do they appear to operate on more than one separate concept?</p>';
    protected $links = ['https://en.wikipedia.org/wiki/Cohesion_(computer_science)'];
}
