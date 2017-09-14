<?php

namespace Hal\Application\Config\File;

use Hal\Application\Config\Config;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;

class ConfigFileReaderJson implements ConfigFileReaderInterface
{
    /**
     * @var string
     */
    private $filename;

    /**
     * ConfigFileReaderJson constructor.
     */
    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    /**
     * @param Config $config
     *
     * @return void
     */
    public function read(Config $config)
    {
        $jsonText = file_get_contents($this->filename);

        if (false === $jsonText) {
            throw new \InvalidArgumentException("Cannot read configuration file '{$this->filename}'");
        }

        $jsonData = json_decode($jsonText, true);

        if (false === $jsonData) {
            throw new \InvalidArgumentException("Bad json file '{$this->filename}'");
        }

        $jsonDataImploded = $this->collapseArray($jsonData);

        foreach ($jsonDataImploded as $key => $value) {
            $config->set($key, $value);
        }
    }

    /**
     * Collapses array into a one-dimensional one by imploding nested keys with '-'
     *
     * @param array $arr
     *
     * @return array
     */
    private function collapseArray(array $arr)
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($arr));
        $result = [];
        foreach ($iterator as $leafValue) {
            $keys = [];
            foreach (range(0, $iterator->getDepth()) as $depth) {
                $keys[] = $iterator->getSubIterator($depth)->key();
            }
            $result[join('-', $keys)] = $leafValue;
        }

        return $result;
    }
}
