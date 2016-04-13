<?php
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