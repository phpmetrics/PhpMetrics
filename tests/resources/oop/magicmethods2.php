<?php
class  ModelOne {

}
class MyClass extends ModelOne {


    public function foo() {

    }
    /**
     * After a clone is called on this object, clone our
     * deep objects.
     *
     * @return null
     */
    public function __clone() {
        parent::__clone();
        $this->default_value_type = $this->default_value_type
            ? clone($this->default_value_type)
            : $this->default_value_type;
    }

    public function __construct(
        Context $context,
        string $name,
        UnionType $type,
        int $flags
    ) {
        parent::__construct(
            $context,
            $name,
            $type,
            $flags
        );
    }
}