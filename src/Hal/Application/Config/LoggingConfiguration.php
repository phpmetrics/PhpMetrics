<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Config;

/**
 * Log configuration
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class LoggingConfiguration
{
    /**
     * Datas
     *
     * @var array
     */
    private $datas;

    /**
     * Constructor
     *
     * @param array $datas
     */
    public function __construct(array $datas = array()) {
        $this->datas = array_merge(array('violations'=> array(), 'report' => array(), 'chart' => array()), $datas);
    }

    /**
     * Get target of report by format
     *
     * @param $format
     * @return string|null
     */
    public function getReport($format) {
        return isset($this->datas['report'], $this->datas['report'][$format])
            ? $this->datas['report'][$format]
            : null;
    }

    /**
     * Get target of report by format
     *
     * @param $format
     * @return string|null
     */
    public function getViolation($format) {
        return isset($this->datas['violations'], $this->datas['violations'][$format])
            ? $this->datas['violations'][$format]
            : null;
    }

    /**
     * Get target of report by format
     *
     * @param $format
     * @return string|null
     */
    public function getChart($format) {
        return isset($this->datas['chart'], $this->datas['chart'][$format])
            ? $this->datas['chart'][$format]
            : null;
    }

    /**
     * Set report
     *
     * @param $format
     * @param $path
     * @return $this
     */
    public function setReport($format, $path) {
        $this->datas['report'][$format] = $path;
        return $this;
    }

    /**
     * Set violation
     *
     * @param $format
     * @param $path
     * @return $this
     */
    public function setViolation($format, $path) {
        $this->datas['violations'][$format] = $path;
        return $this;
    }

    /**
     * Set report
     *
     * @param $format
     * @param $path
     * @return $this
     */
    public function setChart($format, $path) {
        $this->datas['chart'][$format] = $path;
        return $this;
    }
}