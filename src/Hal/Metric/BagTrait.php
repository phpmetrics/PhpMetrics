<?php
namespace Hal\Metric;

trait BagTrait
{
    private $name;

    /**
     * @var array
     */
    private $bag = [];

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
        $this->set('name', $name);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function set($key, $value)
    {
        $this->bag[$key] = $value;
        return $this;
    }

    /**
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        return isset($this->bag[$key]);
    }

    /**
     * @param $key
     * @return null
     */
    public function get($key)
    {
        return $this->has($key) ? $this->bag[$key] : null;
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->bag;
    }

    /**
     * @param array $array
     * @return $this
     */
    public function fromArray(array $array)
    {
        foreach ($array as $key => $value) {
            $this->set($key, $value);
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return array_merge($this->all(), ['_type' => get_class($this)]);
    }
}
