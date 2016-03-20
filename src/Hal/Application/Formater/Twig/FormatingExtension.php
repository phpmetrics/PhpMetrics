<?php
namespace Hal\Application\Formater\Twig;

use Hal\Application\Extension\ExtensionService;
use Hal\Application\Rule\Validator;

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
     * @inheritdoc
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('textify', array($this, 'textify'))
            , new \Twig_SimpleFilter('rule', array($this, 'rule'))
        );
    }

    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('extensions_menu', array($this, 'extensionsMenu'), array('is_safe' => array('html')))
            , new \Twig_SimpleFunction('extensions_js', array($this, 'extensionsJs'), array('is_safe' => array('html')))
            , new \Twig_SimpleFunction('extensions_content', array($this, 'extensionsContent'), array('is_safe' => array('html')))
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
     * @param $key
     * @param $v
     * @return string
     */
    public function rule($v, $key)
    {
        return $this->validator->validate($key, $v);
    }

    /**
     * @param ExtensionService $extensions
     * @return string
     */
    public function extensionsMenu(ExtensionService $extensions)
    {
        $html = '';
        foreach($extensions->getRepository()->all() as $extension) {
            $helper = $extension->getReporterHtmlSummary();
            if(!$helper) {
                continue;
            }
            foreach($helper->getMenus() as $name => $label) {
                $html .= sprintf('<li id="link-%s"><a>%s</a></li>', $name, $label);
            }
        }
        return $html;
    }

    /**
     * @param ExtensionService $extensions
     * @return string
     */
    public function extensionsJs(ExtensionService $extensions)
    {
        $html = '';
        foreach($extensions->getRepository()->all() as $extension) {
            $helper = $extension->getReporterHtmlSummary();
            if(!$helper) {
                continue;
            }
            $html .= $helper->renderJs();
        }
        return $html;
    }

    /**
     * @param ExtensionService $extensions
     * @return string
     */
    public function extensionsContent(ExtensionService $extensions)
    {
        $html = '';
        foreach($extensions->getRepository()->all() as $extension) {
            $helper = $extension->getReporterHtmlSummary();
            if(!$helper) {
                continue;
            }
            $html .= $helper->renderHtml();
        }
        return $html;
    }

    /**
     * @inherit
     */
    public function getName()
    {
        return 'hal_formating_extension';
    }
}
