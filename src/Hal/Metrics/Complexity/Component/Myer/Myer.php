<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Metrics\Complexity\Component\Myer;

use Hal\Component\Token\Tokenizer;
use Hal\Metrics\Complexity\Component\McCabe\McCabe;

/**
 * Calculates myer's interval (extension of McCabe cyclomatic complexity)
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Myer {

    /**
     * Tokenizer
     *
     * @var \Hal\Component\Token\Tokenizer
     */
    private $tokenizer;

    /**
     * Constructor
     *
     * @param Tokenizer $tokenizer
     */
    public function __construct(Tokenizer $tokenizer) {
        $this->tokenizer = $tokenizer;

    }

    /**
     * Calculates Myer's interval
     *
     *      Cyclomatic complexity : Cyclomatic complexity + L
     *      where L is the number of logical operators
     *
     * @param string $filename
     * @return Result
     */
    public function calculate($filename)
    {
        $mcCabe = new McCabe($this->tokenizer);
        $result = new Result;

        $tokens = $this->tokenizer->tokenize($filename);

        // Cyclomatic complexity
        $cc = $mcCabe->calculate($filename);

        // Number of operator
        $L = 0;
        $logicalOperators = array(
            T_BOOLEAN_AND => T_BOOLEAN_AND
            , T_LOGICAL_AND => T_LOGICAL_AND
            , T_BOOLEAN_OR => T_BOOLEAN_OR
        , T_LOGICAL_OR => T_LOGICAL_OR
        );
        foreach($tokens as $token) {
            if(isset($logicalOperators[$token->getType()])) {
                $L++;
            }
        }

        $result
            ->setNumberOfOperators($L)
            ->setMcCabe($cc);

        return $result;
    }
}