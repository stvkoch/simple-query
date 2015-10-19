<?php
namespace Simple;

interface ModelInterface {}

class Model implements ModelInterface {
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

    public function alias($alias = null)
    {
        if ($alias) {
            $this->alias = $alias;
        }
        return $this->alias;
    }

    public function table($table = null)
    {
        if ($table) {
            return $this->table;
        }
        return $this->table;
    }

    /**
     * convention how this model call you primary key
     */
    public function pk($pk = null)
    {
        if($pk) {
            $this->pk = $pk;
        }
        return $this->field($this->pk);
    }

    /**
     * convention how this model is represent as foreign key
     * idTablename
     */
    public function fk($modelFk)
    {
        return $this->field(
            $modelFk->pk().ucfirst($modelFk->table())
        );
    }

    public function field($field)
    {
        return $this->alias().'.'.$field;
    }

    public function __toString()
    {
        return $this->table();
    }
}

