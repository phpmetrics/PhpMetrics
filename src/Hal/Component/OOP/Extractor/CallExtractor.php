<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\OOP\Extractor;
use Hal\Component\Token\TokenCollection;


/**
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class CallExtractor implements ExtractorInterface {

    /**
     * @var Searcher
     */
    private $searcher;

    /**
     * Constructor
     *
     * @param Searcher $searcher
     */
    public function __construct(Searcher $searcher)
    {
        $this->searcher = $searcher;
    }

    /**
     * Extract dependency from call
     *
     * @param $n
     * @param TokenCollection $tokens
     * @return string
     * @throws \LogicException
     */
    public function extract(&$n, TokenCollection $tokens)
    {

        $token = $tokens[$n];
        $call = null;
        switch($token->getType()) {
            case T_PAAMAYIM_NEKUDOTAYIM:
                $prev = $n - 1;
                $value = $this->searcher->getUnder(array('::'), $prev, $tokens);
                if ($value === 'parent') {
                    $extendPosition = $this->searcher->getExtendPostition($tokens);
                    $parentName = $this->searcher->getFollowingName($extendPosition, $tokens);
                    $call = $parentName;
                } else if ($value === 'self' || $value === 'static') {
                    $extendPosition = $this->searcher->getClassNamePosition($tokens);
                    $className = $this->searcher->getFollowingName($extendPosition, $tokens);
                    $call = $className;
                } else {
                    $call = $value;
                }
                break;
            case T_NEW:
                $call = $this->searcher->getFollowingName($n, $tokens);
                if(preg_match('!^(\w+)!', $call, $matches)) { // fixes PHP 5.4:    (new MyClass)->foo()
                    $call = $matches[1];
                }
                break;
        }
        if(null === $call) {
            throw new \LogicException('Classname of call not found');
        }
        return $call;
    }
};