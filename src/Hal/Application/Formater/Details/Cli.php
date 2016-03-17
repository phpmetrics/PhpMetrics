<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Formater\Details;
use Hal\Application\Formater\FormaterInterface;
use Hal\Application\Rule\Validator;
use Hal\Component\Bounds\BoundsInterface;
use Hal\Component\Bounds\Result\ResultInterface;
use Hal\Component\Result\ResultCollection;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Formater for cli usage
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Cli implements FormaterInterface {

    /**
     * Validator
     *
     * @var \Hal\Application\Rule\Validator
     */
    private $validator;

    /**
     * Bounds
     *
     * @var BoundsInterface
     */
    private $bound;

    /**
     * Constructor
     *
     * @param Validator $validator
     * @param BoundsInterface $bound
     */
    public function __construct(Validator $validator, BoundsInterface $bound)
    {
        $this->bound = $bound;
        $this->validator = $validator;
    }

    /**
     * @inheritdoc
     */
    public function terminate(ResultCollection $collection, ResultCollection $groupedResults){

        $output = new BufferedOutput(OutputInterface::VERBOSITY_NORMAL, true);
//        $output->write(str_pad("\x0D", 80, "\x20"));
//        $output->writeln('');


        // overview
        $total = $this->bound->calculate($collection);
        $output->writeln(sprintf(
            '<info>%d</info> files have been analyzed. Read and understand these <info>%s</info> lines of code will take around <info>%s</info>.'
            , sizeof($collection, COUNT_NORMAL)
            , $total->getSum('loc')
            , $this->formatTime($total->getSum('time'))
        ));


        $output->writeln('<info>Average for each module:</info>');
        $output->writeln('');

        $hasOOP = null !== $total->getSum('instability');

        $table = new \Symfony\Component\Console\Helper\Table($output);
        $table
            ->setHeaders(array_merge(
                array(
                    'Name'
                    , 'Complexity'
                    , 'Myer Distance'
                    , 'Maintainability'
                    , 'LLOC'
                    , 'Comment weight'
                    , 'Vocabulary'
                    , 'Volume'
                    , 'Bugs'
                    , 'Difficulty'
                )
                , ($hasOOP ? array(
                    'lcom'
                    , 'SysComplexity'
                    , 'Instability'
                    , 'Abstractness'
                    , 'ce'
                    , 'ca'
                    ) : array())
            ));

        foreach($groupedResults as $result) {
            $table->addRow(array_merge(
                array(
                    str_repeat('  ', $result->getDepth()).$result->getName()
                    , $this->getRow($result->getBounds(), 'cyclomaticComplexity', 'average', 0)
                    , $this->getRow($result->getBounds(), 'myerDistance', 'average', 0)
                    , $this->getRow($result->getBounds(), 'maintainabilityIndex', 'average', 0)
                    , $this->getRow($result->getBounds(), 'logicalLoc', 'sum', 0)
                    , $this->getRow($result->getBounds(), 'commentWeight', 'average', 0)
                    , $this->getRow($result->getBounds(), 'vocabulary', 'average', 0)
                    , $this->getRow($result->getBounds(), 'volume', 'average', 0)
                    , $this->getRow($result->getBounds(), 'bugs', 'sum', 2)
                    , $this->getRow($result->getBounds(), 'difficulty', 'average', 0)
                )
                , ($hasOOP ? array(
                    $this->getRow($result->getBounds(), 'lcom', 'average', 2)
                    , $this->getRow($result->getBounds(), 'rsysc', 'average', 2)
                    , $result->getInstability()->getInstability()
                    , $result->getAbstractness()->getAbstractness()
                    , $this->getRow($result->getBounds(), 'efferentCoupling', 'average', 2)
                    , $this->getRow($result->getBounds(), 'afferentCoupling', 'average', 2)
                    ) : array())
                )
            );
        }
        $table->render();

        return $output->fetch();
    }

    /**
     * Get formated row
     *
     * @param ResultInterface $bound
     * @param string $key
     * @param string $type
     * @param integer $round
     * @return string
     */
    private function getRow(ResultInterface $bound, $key, $type, $round) {
        $value = $bound->get($type, $key);
        $value = !is_null($value) ? round($bound->get($type, $key), $round) : '?';
        return sprintf('<%1$s>%2$s</%1$s>', $this->getStyle(($type == 'average' ? $key : null), $value), $value);
    }

    /**
     * Get style, according score
     *
     * @param string $key
     * @param double $value
     * @return string
     */
    private function getStyle($key, $value) {
        $score = $this->validator->validate($key, $value);

        switch($score) {
            case Validator::GOOD:
                return 'fg=green';
            case Validator::WARNING:
                return 'bg=yellow;fg=black';
            case Validator::CRITICAL:
                return 'bg=red;fg=white';
        }
        return 'fg=white';
    }

    /**
     * Format duration in seconds into text representation
     *
     * @param null|double $duration Duration in seconds
     * @return string
     */
    private function formatTime($duration) {
        $duration = abs((int) $duration);
        return sprintf(
            '%s hour(s), %s minute(s) and %s second(s)'
            , floor($duration / 3600)
            , ($duration / 60) % 60
            , $duration % 60
        );
    }

    /**
     * @inheritdoc
     */
    public function getName() {
        return 'Detailled CLI';
    }
}
