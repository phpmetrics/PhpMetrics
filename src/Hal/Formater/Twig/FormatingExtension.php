<?php
namespace Hal\Formater\Twig;

use Hal\Rule\Validator;

class FormatingExtension extends \Twig_Extension
{
    /**
     * @var Validator
     */
    private $validator;

    /**
     * Validator
     *
     * @param Validator $validator
     */
    function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @inherit
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('textify', array($this, 'textify'))
            , new \Twig_SimpleFilter('rule', array($this, 'rule'))
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
     * Check value according rule
     *
     * @param $v
     * @return string
     */
    public function rule($v, $key)
    {
        return $this->validator->validate($key, $v);
    }

    /**
     * @inherit
     */
    public function getName()
    {
        return 'hal_formating_extension';
    }
}
