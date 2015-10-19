<?php
namespace Simple;

class Model {
    use Traits\Model;


    public function __construct($options = [])
    {
        if (isset($options['table'])) {
            $this->table = $options['table'];
            $this->alias = $options['table'];
        }
        if (isset($options['alias'])) {
            $this->alias = $options['alias'];
        }
        if (isset($options['pk'])) {
            $this->pk = $options['pk'];
        }
    }
}

