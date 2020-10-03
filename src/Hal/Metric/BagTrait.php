<?php
namespace Hal\Metric;

trait BagTrait
{
    /** @var string */
    private $name;

    /**
     * @var array<string,mixed>
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
     * @param string $key
     * @param mixed $value
     * @return static
     */
    public function set($key, $value)
    {
        $this->bag[$key] = $value;
        return $this;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return isset($this->bag[$key]);
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function get($key)
    {
        return $this->has($key) ? $this->bag[$key] : null;
    }

    /**
     * @return array<string,mixed>
     */
    public function all()
    {
        return $this->bag;
    }

    /**
     * @param array<string,mixed> $array
     * @return static
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
    public function jsonSerialize()
    {
        return array_merge($this->all(), ['_type' => get_class($this)]);
    }
}
