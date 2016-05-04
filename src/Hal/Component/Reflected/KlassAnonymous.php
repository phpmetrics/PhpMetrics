<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Reflected;


class KlassAnonymous extends Klass
{

    /**
     * @inheritdoc
     */
    public function isAnonymous()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'class@anonymous';
    }
}