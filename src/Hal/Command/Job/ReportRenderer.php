<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Command\Job;
use Hal\Formater\FormaterInterface;
use Hal\Result\ResultCollection;
use Hal\Rule\Validator;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;


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
     * Constructor
     *
     * @param OutputInterface $output
     * @param FormaterInterface $formater
     */
    function __construct(OutputInterface $output, FormaterInterface $formater)
    {
        $this->output = $output;
        $this->formater = $formater;
    }

    /**
     * @inheritdoc
     */
    public function execute(ResultCollection $collection) {
        $this->output->write($this->formater->terminate($collection));
    }

}
