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
 * Job report
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class ReportWriter implements JobInterface
{

    /**
     * Destination
     *
     * @var string
     */
    private $destination;

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
     * @param $destination
     * @param OutputInterface $output
     * @param FormaterInterface $formater
     */
    function __construct($destination, OutputInterface $output, FormaterInterface $formater)
    {
        $this->destination = $destination;
        $this->output = $output;
        $this->formater = $formater;
    }

    /**
     * @inheritdoc
     */
    public function execute(ResultCollection $collection) {

        if(!$this->destination) {
            return;
        }

        $dir = dirname($this->destination);
        if(!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        $this->output->writeln('Generating Detailled HTML Report...');
        $handle = fopen($this->destination, 'w');
        $stream = new StreamOutput($handle);
        $stream->write($this->formater->terminate($collection));
        fclose($handle);
    }

}
