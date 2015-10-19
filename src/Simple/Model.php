<?php
namespace Simple;

class Model {
    use Traits\Model;

    protected $table = null;
    protected $alias = null;
    protected $pk = 'id';

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

