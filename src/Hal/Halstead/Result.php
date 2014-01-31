<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Halstead;
use Hal\Result\ExportableInterface;

/**
 * Representation of Halstead complexity
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Result implements ExportableInterface {
    /**
     * Length of a program
     *
     * @var integer
     */
    private $length;

    /**
     * Vocabulary
     *
     * @var integer
     */
    private $vocabulary;

    /**
     * Volume
     *
     * @var integer
     */
    private $volume;

    /**
     * Difficulty
     *
     * @var float
     */
    private $difficulty;

    /**
     * Effort
     *
     * @var float
     */
    private $effort;

    /**
     * Bugs expected
     *
     * @var float
     */
    private $bugs;

    /**
     * Time
     *
     * @var integer
     */
    private $time;

    /**
     * Intelligent content
     *
     * @var integer
     */
    private $intelligentContent;

    /**
     * @inheritdoc
     */
    public function asArray() {
        return array(
            'volume' => $this->getVolume()
            ,'length' => $this->getLength()
            ,'vocabulary' => $this->getVocabulary()
            ,'effort' => $this->getEffort()
            ,'difficulty' => (string) $this->getDifficulty()
            ,'time' => $this->getTime()
            ,'bugs' => $this->getBugs()
            ,'intelligentContent' => $this->getIntelligentContent()
        );
    }

    /**
     * @param float $bugs
     */
    public function setBugs($bugs)
    {
        $this->bugs = $bugs;
        return $this;
    }

    /**
     * @return float
     */
    public function getBugs()
    {
        return $this->bugs;
    }

    /**
     * @param float $difficulty
     */
    public function setDifficulty($difficulty)
    {
        $this->difficulty = $difficulty;
        return $this;
    }

    /**
     * @return float
     */
    public function getDifficulty()
    {
        return $this->difficulty;
    }

    /**
     * @param float $effort
     */
    public function setEffort($effort)
    {
        $this->effort = $effort;
        return $this;
    }

    /**
     * @return float
     */
    public function getEffort()
    {
        return $this->effort;
    }

    /**
     * @param int $length
     */
    public function setLength($length)
    {
        $this->length = $length;
        return $this;
    }

    /**
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param int $time
     */
    public function setTime($time)
    {
        $this->time = $time;
        return $this;
    }

    /**
     * @return int
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param int $vocabulary
     */
    public function setVocabulary($vocabulary)
    {
        $this->vocabulary = $vocabulary;
        return $this;
    }

    /**
     * @return int
     */
    public function getVocabulary()
    {
        return $this->vocabulary;
    }

    /**
     * @param int $volume
     */
    public function setVolume($volume)
    {
        $this->volume = $volume;
        return $this;
    }

    /**
     * @return int
     */
    public function getVolume()
    {
        return $this->volume;
    }

    /**
     * @return float
     */
    public function getIntelligentContent() {
        return $this->intelligentContent;
    }

    /**
     * @param float $intelligentContent
     */
    public function setIntelligentContent($intelligentContent)
    {
        $this->intelligentContent = $intelligentContent;
    }
}