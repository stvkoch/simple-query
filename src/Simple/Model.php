<?php
namespace Simple;

class Model {
    use Traits\Model;


    public function __construct($options = [])
    {
        $this->init($options);
    }
}

