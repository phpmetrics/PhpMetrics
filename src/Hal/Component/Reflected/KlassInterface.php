<?php
namespace Hal\Component\Reflected;


class KlassInterface extends Klass
{

    /**
     * @inheritdoc
     */
    public function isInterface()
    {
        return true;
    }
}