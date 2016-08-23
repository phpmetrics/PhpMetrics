<?php
namespace Hal\Metric\Information;

abstract class AbstractInformation
{
    const ID = 'undefined';
    const LINK_TEMPLATE = '/information/metric#{metricID}';

    /** @var string */
    protected $name = '';

    /** @var string */
    protected $shortDescription = '';

    /** @var string */
    protected $longDescription = '';

    /** @var string[] */
    protected $links = [];

    /** @var string if empty, this is a base metric that is not calculated from any other */
    protected $formula = '';

    /**
     * unique symbolic identifier used in BagTrait to store values for this metric
     * @return string
     */
    public function getID()
    {
        return static::ID;
    }

    /**
     * short descriptive name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * A one line description suitable for html title mouse over hints
     * @return string plain text
     */
    public function getShortDescription()
    {
        return $this->shortDescription;
    }

    /**
     * A longer description containing html
     * @return string html including p tags
     */
    public function getLongDescription()
    {
        return $this->longDescription;
    }

    /**
     * return a url to the documentation for this
     * @param null $metricID
     * @return string
     */
    public function getUrl($metricID = null)
    {
        if (empty($metricID)) {
            $metricID = $this->getID();
        }
        return str_replace('{metricID}', self::LINK_TEMPLATE, $metricID);
    }

    /**
     * return a formula in html with links to the other metrics that it is calculated from (if any)
     * @return string
     */
    public function renderFormulaToHtml()
    {
        $result = [];
        $parts  = preg_split('|[\s+\(\)]+|', $this->formula);
        foreach ($parts as $part) {
            if (ctype_alpha($part)) {
                // turn the variable in the formula into a link to its documentation
                $part = '<a href="' . $this->getUrl($part) . '">' . $part . '</a>';
            }
            $result[] = $part;
        }
        return join(' ', $result);
    }

    /**
     * links are title => url. If is_numeric(title) then the url is the title.
     * Assumes no escaping needed because links in the source code are safe.
     * @return array raw url => a href tag
     */
    public function renderLinksToHtml()
    {
        $links = [];
        foreach ($this->links as $title => $url) {
            if (is_numeric($title)) {
                $title = $url;
            }
            $links[$url] = '<a href="' . $url . '">' . $title . '</a>';
        }
        return $links;
    }

    /**
     * return html documentation for this metric. Use this in a loop to iterate all metrics and output html
     * documentation page(s)
     * @return string html
     */
    public function renderToHtml()
    {
        $output  = '<h2>' . $this->getName() . '</h2>' .
            '<p><em>' . $this->getShortDescription() . '</em></p>' .
            $this->getLongDescription();
        $formula = $this->renderFormulaToHtml();
        if (!empty($formula)) {
            $output .= '<p>' . $formula . '</p>';
        }
        $links = $this->renderLinksToHtml();
        if (!empty($links)) {
            $output .= '<ul><li>' . join('</li><li>', $links) . '</li>';
        }
        return $output;
    }
}
