<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Metrics\Complexity\Text\Length;
use Hal\Metrics\CodeMetric;

/**
 * Calculates McCaybe measure
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Loc implements CodeMetric
{

    /**
     * Calculates Lines of code
     *
     * @param $code
     * @return Result
     */
    public function calculate($code)
    {
        // count all lines
        $loc = sizeof(preg_split('/\r\n|\r|\n/', $code)) - 1;

        // count and remove multi lines comments
        $cloc = 0;
        if(preg_match_all('!/\*.*?\*/!s', $code, $matches)) {
            foreach($matches[0] as $match) {
                $cloc +=  max(1, sizeof(preg_split('/\r\n|\r|\n/', $match)));
            }
        }
        $code = preg_replace('!/\*.*?\*/!s', '', $code);

        // count and remove single line comments
        $code = preg_replace('!(\n//.+\n)!', '', $code, -1, $nbCommentsSingleLine);
        $cloc += $nbCommentsSingleLine;

        // count and remove empty lines
        $code = trim(preg_replace('!(^\s*[\r\n])!sm', '', $code));
        $lloc = sizeof(preg_split('/\r\n|\r|\n/', $code)) ;

        $result = new Result;
        $result
            ->setLoc($loc)
            ->setCommentLoc($cloc)
            ->setLogicalLoc($lloc)
        ;

        return $result;
    }
}
