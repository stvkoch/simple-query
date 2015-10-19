<?php
namespace Simple\Traits;

trait Model {

    protected $table = null;
    protected $alias = null;
    protected $pk = 'id';

    public function init($options = [])
    {
    }

    public function alias($alias = null)
    {
        if ($alias) {
            $this->alias = $alias;
        }

        if(is_null($this->alias)){
            return $this->table();
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
        return $this->pk;
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
}

