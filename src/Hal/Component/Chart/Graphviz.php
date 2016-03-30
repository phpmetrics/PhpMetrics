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
     * @return boolean
     */
    public function isAvailable() {
        $result = shell_exec('circo -V 2>&1');
        return preg_match('!graphviz version!', $result);
    }

    /**
     * Get image content
     *
     * @param string $dotContent
     * @return string
     */
    public function getImage($dotContent) {

        if(!$this->isAvailable()) {
            throw new \RuntimeException('Graphviz not installed');
        }

        $dotFile = tempnam(sys_get_temp_dir(), 'phpmetrics-graphviz');
        $imageFile = tempnam(sys_get_temp_dir(), 'phpmetrics-graphviz');

        // dot file
        $dotContent = str_replace('\\', '/', $dotContent);
        file_put_contents($dotFile, $dotContent);

        // image
        shell_exec(sprintf('circo -Lg -Tsvg -o%2$s %1$s  2>&1', $dotFile, $imageFile));
        $content = file_get_contents($imageFile);
        unlink($imageFile);
        unlink($dotFile);
        return $content;
    }
}