<?php
namespace Halstead;

class Halstead {

    private $operators = array();
    private $operands = array();
    private $tokenType;

    public function __construct(\TokenType $tokenType)
    {
        $this->tokenType = $tokenType;
    }

    public function inventory(\Token $token)
    {
        if($this->tokenType->isOperand($token)) {
            $this->operands[] = $token;
        }
        else if($this->tokenType->isOperator($token)) {
            $this->operators[] = $token;
        }
        return $this;
    }

    public function calculate()
    {
        $result = new Result;

        $uniqueOperators = array_map( 'unserialize', array_unique( array_map( 'serialize', $this->operators ) ) );
        $uniqueOperands = array_map( 'unserialize', array_unique( array_map( 'serialize', $this->operands ) ) );

        $n1 = sizeof($uniqueOperands);
        $n2 = sizeof($uniqueOperators);
        $N1 = sizeof($this->operands);
        $N2 = sizeof($this->operators);

        $V = ($N1 + $N2)  * log($n1 +  $n2, 2);
        $D = ($n1 / $N2) / (2 / $n2);
        $E = $D * $V;
        $B = $E * 0.667 / 3000;
        $T = $E / 18;


        $result
            ->setLength($N1 + $N2)
            ->setVocabulary($n1 + $n2)
            ->setVolume($V)
            ->setDifficulty($D)
            ->setEffort($E)
            ->setBugs($B)
            ->setTime($T)
        ;

        return $result;

    }
}