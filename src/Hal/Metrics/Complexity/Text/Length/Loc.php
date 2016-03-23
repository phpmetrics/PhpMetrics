<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Metrics\Complexity\Text\Length;
use Hal\Component\Token\TokenCollection;

/**
 * Calculates McCaybe measure
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Loc {

    /**
     * Calculates Lines of code
     *
     * @param string $filename
     * @param TokenCollection $tokens
     * @return Result
     */
    public function calculate($filename, $tokens)
    {

        $info = new Result;

        $cloc = $lloc = 0;
        foreach($tokens as $token) {

            switch($token->getType()) {
                case T_STRING:
                    if(';' == $token->getValue()) {
                        $lloc++;
                    }
                    break;
                case T_COMMENT:
                    $cloc++;
                    break;
                case T_DOC_COMMENT:
                    $cloc += count(preg_split('/\r\n|\r|\n/', $token->getValue()));
                    break;
            }
        }

        $content = file_get_contents($filename);
        $info
            ->setLoc(count(preg_split('/\r\n|\r|\n/', $content)) - 1)
            ->setCommentLoc($cloc)
            ->setLogicalLoc($lloc)
        ;

        return $info;
    }
}
