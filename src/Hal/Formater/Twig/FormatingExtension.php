<?php
namespace Hal\Formater\Twig;

class FormatingExtension extends \Twig_Extension
{
    /**
     * @inherit
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('textify', array($this, 'textify'))
        );
    }

    /**
     * String as readable text
     *
     * @param $v
     * @return string
     */
    public function textify($v)
    {
        return ucfirst(preg_replace( '/([a-z0-9])([A-Z])/', "$1 $2", $v ));
    }

    /**
     * @inherit
     */
    public function getName()
    {
        return 'hal_formating_extension';
    }
}
