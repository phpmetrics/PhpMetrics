<?php
class Token {
    private $type;

    public function __construct( $data)
    {
        if(!is_array($data)) {
            $data = array($data);
        }
        $this->type = $data[0];
    }

    public function getType()
    {
        return $this->type;
    }
}