<?php
namespace Hal\Component\Parser;

class Searcher
{

    /**
     * @param $tokens
     * @param $current
     * @param $required
     * @return bool
     */
    public function getNext($tokens, $current, $required)
    {
        $len = sizeof($tokens);
        for ($i = $current; $i < $len; $i++) {
            if ($required === $tokens[$i]) {
                return $i;
            }
        }
        return false;
    }

    /**
     * @param $tokens
     * @param $current
     * @param $required
     * @return bool
     */
    public function getPrevious($tokens, $current, $required)
    {
        for ($i = $current; $i >= 0; $i--) {
            if ($required === $tokens[$i]) {
                return $i;
            }
        }
        return false;
    }

    /**
     * @param $tokens
     * @param $startingToken
     * @return null
     */
    public function getPositionOfClosingBrace($tokens, $startingToken)
    {
        $openBrace = 0;
        $start = null;
        $len = sizeof($tokens);
        for ($i = $startingToken; $i < $len; $i++) {
            $token = $tokens[$i];
            if (Token::T_BRACE_CLOSE === $token) {
                $openBrace--;
                if ($openBrace <= 0) {
                    return $i;
                }
            }
            if (Token::T_BRACE_OPEN === $token) {
                $openBrace++;
                if (is_null($start)) {
                    $start = $startingToken = $i + 1;
                }
            }
        }

        return false;
    }
}