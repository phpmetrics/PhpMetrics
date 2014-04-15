<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Formater\Summary;
use Hal\Component\Bounds\Bounds;
use Hal\Component\Bounds\BoundsAgregateInterface;
use Hal\Component\Bounds\BoundsInterface;
use Hal\Component\Bounds\Result\ResultInterface;
use Hal\Application\Formater\FormaterInterface;
use Hal\Component\Result\ResultCollection;
use Hal\Application\Rule\Validator;
use Symfony\Component\Console\Helper\TableHelper;
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
     * AgregateBounds
     *
     * @var BoundsInterface
     */
    private $agregateBounds;

    /**
     * Constructor
     *
     * @param Validator $validator
     * @param BoundsInterface $bound
     * @param BoundsAgregateInterface $agregateBounds
     */
    public function __construct(Validator $validator, BoundsInterface $bound, BoundsAgregateInterface $agregateBounds)
    {
        $this->bound = $bound;
        $this->agregateBounds = $agregateBounds;
        $this->validator = $validator;
    }

    /**
     * @inheritdoc
     */
    public function terminate(ResultCollection $collection){

        $output = new BufferedOutput(OutputInterface::VERBOSITY_NORMAL, true);
        
        // overview
        $total = $this->bound->calculate($collection);
        $output->writeln(sprintf(
            '<info>%d</info> files have been analyzed. Read and understand these <info>%s</info> lines of code will take around <info>%s</info>.'
            , sizeof($collection, COUNT_NORMAL)
            , $total->getSum('loc')
            , $this->formatTime($total->getSum('time'))
        ));


        // by directory
        $directoryBounds = $this->agregateBounds->calculate($collection);


        $output->writeln('<info>Avegare for each module:</info>');
        $output->writeln('');

        $table = new TableHelper();
        $table
            ->setHeaders(array(
                'Directory'
                , 'LOC'
                , 'Complexity'
                , 'Maintenability'
                , 'LLOC'
                , 'Comment weight'
                , 'Vocabulary'
                , 'Volume'
                , 'Bugs'
                , 'Difficulty'
                , 'Instability'
                , 'CE'
                , 'CA'
            ))
            ->setLayout(TableHelper::LAYOUT_DEFAULT);

        foreach($directoryBounds as $directory => $bound) {
            $table->addRow(array(
                str_repeat('  ', $bound->getDepth()).$directory
                , $this->getRow($bound, 'loc', 'sum', 0)
                , $this->getRow($bound, 'cyclomaticComplexity', 'average', 0)
                , $this->getRow($bound, 'maintenabilityIndex', 'average', 0)
                , $this->getRow($bound, 'logicalLoc', 'average', 0)
                , $this->getRow($bound, 'commentWeight', 'average', 0)
                , $this->getRow($bound, 'vocabulary', 'average', 0)
                , $this->getRow($bound, 'volume', 'average', 0)
                , $this->getRow($bound, 'bugs', 'average', 2)
                , $this->getRow($bound, 'difficulty', 'average', 0)
                , $this->getRow($bound, 'instability', 'average', 2)
                , $this->getRow($bound, 'efferentCoupling', 'average', 2)
                , $this->getRow($bound, 'afferentCoupling', 'average', 2)
            ));
        }
        $table->render($output);

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
        return sprintf('<%1$s>%2$s</%1$s>', $this->getStyle($key, $value), $value);
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
     * Format time in text
     *
     * @param null|double $v
     * @return string
     */
    private function formatTime($v) {
        return sprintf('%s hour(s), %s minute(s) and %s second(s)'
            , gmdate('H', $v)
            , gmdate('m', $v)
            , gmdate('s', $v)
        );
    }

    /**
     * @inheritdoc
     */
    public function getName() {
        return 'Summary CLI';
    }
}