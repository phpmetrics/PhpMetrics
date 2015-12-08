<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\OOP\Extractor;
use Hal\Component\OOP\Reflected\ReflectedClass;
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
     * @var ReflectedClass
     */
    private $currentClass;

    /**
     * Constructor
     *
     * @param Searcher $searcher
     * @param ReflectedClass $currentClass
     */
    public function __construct(Searcher $searcher, ReflectedClass $currentClass = null)
    {
        $this->searcher = $searcher;
        $this->currentClass = $currentClass;
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

                switch(true) {
                    case $value == 'parent' && $this->currentClass:
                        return $this->currentClass->getParent();

                    case $value == 'parent':
                        // we try to get the name of the parent class
                        $extendPosition = $this->searcher->getExtendPosition($tokens, $n);
                        return $this->searcher->getFollowingName($extendPosition, $tokens);

                    case ($value == 'static' ||$value === 'self')&& $this->currentClass:
                        return $this->currentClass->getFullname();

                    case ($value == 'static' ||$value === 'self'):
                        $extendPosition = $this->searcher->getClassNamePosition($tokens);
                        return $this->searcher->getFollowingName($extendPosition, $tokens);

                    default:
                        return $value;
                }

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