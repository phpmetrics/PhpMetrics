<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Command\Job;
use Hal\Application\Formater\FormaterInterface;
use Hal\Component\Result\ResultCollection;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Job report renderer
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class ReportRenderer implements JobInterface
{

    /**
     * Formater
     *
     * @var FormaterInterface
     */
    private $formater;

    /**
     * Output
     *
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    private $output;

    /**
     * Is enabled ?
     *
     * @var bool
     */
    private $enabled = false;

    /**
     * Constructor
     *
     * @param boolean $enabled
     * @param OutputInterface $output
     * @param FormaterInterface $formater
     */
    function __construct($enabled, OutputInterface $output, FormaterInterface $formater)
    {
        $this->output = $output;
        $this->enabled = $enabled;
        $this->formater = $formater;
    }

    /**
     * @inheritdoc
     */
    public function execute(ResultCollection $collection, ResultCollection $aggregatedResults) {
        if(!$this->enabled) {
            return;
        }

        $this->output->write($this->formater->terminate($collection, $aggregatedResults));
    }

}
