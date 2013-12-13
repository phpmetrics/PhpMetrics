<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Loc;

/**
 * Calculates Lines of code
 *
 * @uses SebastianBergmann\PHPLOC\Analyser
 * @link https://github.com/sebastianbergmann/phploc
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Loc {

    /**
     * Calculates Lines of code
     *
     * @param string $file
     * @return Result
     */
    public function calculate($file)
    {

        $files = array($file);
        $analyser = new \SebastianBergmann\PHPLOC\Analyser();
        $data = $analyser->countFiles($files, false);

        $info = new Result;
        $info
            ->setLoc($data['loc'])
            ->setLogicalLoc($data['ncloc'])
            ->setComplexityCyclomatic($data['ccn']);

        return $info;
    }
}