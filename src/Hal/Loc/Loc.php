<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Loc;
use Hal\Token\Token;

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
     * @return Result
     */
    public function calculate($filename)
    {

        $info = new Result;
        $content = file_get_contents($filename);
        $tokens = token_get_all($content);

        $cloc = $lloc = 0;
        foreach($tokens as $data) {
            $token = new Token($data);

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
                    $cloc += substr_count($token->getValue(), PHP_EOL) + 1;
                    break;
            }
        }

        $info
            ->setLoc(substr_count($content, PHP_EOL))
            ->setCommentLoc($cloc)
            ->setLogicalLoc($lloc)
        ;

        return $info;
    }
}