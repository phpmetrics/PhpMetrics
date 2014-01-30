<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Halstead;


/**
 * Calculates Halstead complexity
 *
 *      According Wikipedia, "Halstead complexity measures are software metrics introduced by Maurice Howard Halstead in
 *      1977 as part of his treatise on establishing an empirical science of software development.
 *      Halstead makes the observation that metrics of the software should reflect the implementation or
 *      expression of algorithms in different languages, but be independent of their execution on a specific platform.
 *      These metrics are therefore computed statically from the code."
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Halstead {

    /**
     * Operators
     *
     * @var array
     */
    private $operators = array();

    /**
     *
     * Operands
     *
     * @var array
     */
    private $operands = array();

    /**
     * Allows to determine what is the type of tokens
     *
     * @var \TokenType
     */
    private $tokenType;

    /**
     * Constructor
     *
     * @param \Hal\Token\TokenType $tokenType
     */
    public function __construct(\Hal\Token\TokenType $tokenType)
    {
        $this->tokenType = $tokenType;
    }

    /**
     * Inventories tokens
     *
     * @param string $filename
     * @return $this
     */
    private function inventory($filename)
    {
        $this->operators = $this->operands = array();
        $tokens = token_get_all(file_get_contents($filename));

        foreach($tokens as $data) {
            $token = new \Hal\Token\Token($data);
            if($this->tokenType->isOperand($token)) {
                $this->operands[] = $token;
            }
            else if($this->tokenType->isOperator($token)) {
                $this->operators[] = $token;
            }
        }
        return $this;
    }

    /**
     * Calculate Halstead metrics
     *
     * @param $filenameN
     * @return Result
     */
    public function calculate($filename)
    {
        $this->inventory($filename);
        $result = new Result;

        $uniqueOperators = array_map( 'unserialize', array_unique( array_map( 'serialize', $this->operators ) ) );
        $uniqueOperands = array_map( 'unserialize', array_unique( array_map( 'serialize', $this->operands ) ) );

        $n1 = sizeof($uniqueOperands, COUNT_NORMAL);
        $n2 = sizeof($uniqueOperators, COUNT_NORMAL);
        $N1 = sizeof($this->operands, COUNT_NORMAL);
        $N2 = sizeof($this->operators, COUNT_NORMAL);

        if(($n2 == 0)||($N2 == 0)||($n2 == 2)) {
            // files without operators
            $V = $n1 = $n2 = $N1 = $N2 = $E = $D = $B = $T = 0;
        } else {
            // "normal" file
            $V = ($N1 + $N2)  * log($n1 +  $n2, 2);
            $D = ($n1 / $N2) / (2 / $n2);
            $E = $D * $V;
            $B = $E * 0.667 / 3000;
            $T = $E / 18;
        }


        $result
            ->setLength($N1 + $N2)
            ->setVocabulary($n1 + $n2)
            ->setVolume($V)
            ->setDifficulty($D)
            ->setEffort(round($E,2))
            ->setBugs(round($B, 2))
            ->setTime(round($T))
        ;

        return $result;
    }
}