<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Token;
use Hal\Component\Cache\Cache;
use Hal\Component\Cache\CacheNull;

/**
 * Tokenize file
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Tokenizer {

    private $cache;

    /**
     * Tokenizer constructor.
     * @param $cache
     */
    public function __construct(Cache $cache = null)
    {
        if(null == $cache) {
            $cache = new CacheNull();
        }
        $this->cache = $cache;
    }


    /**
     * Tokenize file
     *
     * @param $filename
     * @return TokenCollection
     */
    public function tokenize($filename) {

        if($this->cache->has($filename)) {
            return new TokenCollection($this->cache->get($filename));
        }

        $size = filesize($filename);
        $limit = 102400; // around 100 Ko
        if($size > $limit) {
            $tokens = $this->tokenizeLargeFile($filename);
        } else {
            $tokens = token_get_all($this->cleanup(file_get_contents($filename)));
        }

        $this->cache->set($filename, $tokens);
        return new TokenCollection($tokens);
    }

    /**
     * Tokenize large files
     *
     * @param $filename
     * @return TokenCollection
     */
    protected function tokenizeLargeFile($filename) {
        // run in another process to allow catching fatal errors due to memory issues with "token_get_all()" function
        // @see https://github.com/Halleck45/PhpMetrics/issues/139
        // @see https://github.com/Halleck45/PhpMetrics/issues/13
        $code = <<<EOT
\$c = file_get_contents("$filename");
\$c = preg_replace("!(<\?\s)!", "<?php ", \$c);
echo serialize(token_get_all(\$c));
EOT;
        $output = shell_exec('php -r \'%s\'', $code);
        $tokens = unserialize($output);
        if(false === $tokens) {
            throw new NoTokenizableException(sprintf('Cannot tokenize "%s". This file is probably too big. Please try to increase memory_limit', $filename));
        }
        return $tokens;
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