<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Chart;


/**
 * Generate graphs
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Graphviz
{
    /**
     * Checks installation of graphviz
     *
     * @throws \RuntimeException
     */
    public function checkInstallation() {
        $result = shell_exec('circo -V 2>&1');
        if(!preg_match('!graphviz version!', $result)) {
            throw new \RuntimeException('Graphviz not installed');
        }
    }

    /**
     * Get image content
     *
     * @param $dotContent
     * @return string
     */
    public function getImage($dotContent) {

        $this->checkInstallation();

        $dotFile = tempnam(sys_get_temp_dir(), 'phpmetrics-graphviz');
        $imageFile = tempnam(sys_get_temp_dir(), 'phpmetrics-graphviz');

        // dot file
        file_put_contents($dotFile, $dotContent);
        file_put_contents('/tmp/tmp.gv', $dotContent);

        // image
        shell_exec(sprintf('circo -Lg -Tsvg -o%2$s %1$s  2>&1', $dotFile, $imageFile));
        $content = file_get_contents($imageFile);
        unlink($imageFile);
        unlink($dotFile);
        return $content;
    }
}