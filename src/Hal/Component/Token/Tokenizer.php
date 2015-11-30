<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Token;

/**
 * Tokenize file
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Tokenizer {

    /**
     * Tokenize file
     *
     * @param $filename
     * @return TokenCollection
     */
    public function tokenize($filename) {
        //
        // fixes memory problems with large files
        // https://github.com/Halleck45/PhpMetrics/issues/13
        $size = filesize($filename);
        $limit = 102400; // around 100 Ko
        if($size > $limit) {
            $tokens = array();
            $hwnd = fopen($filename, 'r');
            while (!feof($hwnd)) {
                $content = stream_get_line($hwnd, $limit);
                // string is arbitrary splitted, so content can be incorrect
                // for example: "Unterminated comment starting..."
                $content .= '/* */';
                $tokens = array_merge($tokens, token_get_all($this->cleanup($content)));
                unset($content);
            }
            return new TokenCollection($tokens);
        }

        return new TokenCollection(token_get_all($this->cleanup(file_get_contents($filename))));
    }

    /**
     * Clean php source
     *
     * @param $content
     * @return string
     */
    private function cleanup($content) {
        // replacing short open tags by <?php
        // if file contains short open tags but short_open_tags='Off' in php.ini bug can occur
        // @see https://github.com/Halleck45/PhpMetrics/issues/154
        return preg_replace('!(<\?\s)!', '<?php ', $content);
    }

}