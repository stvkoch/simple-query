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
        if ($options['alias']) {
            $this->alias = $options['alias'];
        }
        if ($options['pk']) {
            $this->pk = $options['pk'];
        }
    }
}

