<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Extension;

class Repository{

    /**
     * @var array
     */
    private $extensions = array();

    /**
     * @param Extension $extension
     * @return $this
     */
    public function attach(Extension $extension) {
        $this->extensions[$extension->getName()] = $extension;
        return $this;
    }

    /**
     * @param Extension $extension
     * @return $this
     */
    public function detach(Extension $extension) {
        unset($this->extensions[$extension->getName()]);
        return $this;
    }

    /**
     * @param Extension $extension
     * @return bool
     */
    public function has(Extension $extension)
    {
        return isset($this->extensions[$extension->getName()]);
    }

    /**
     * @return Extension[]
     */
    public function all()
    {
        return $this->extensions;
    }
}