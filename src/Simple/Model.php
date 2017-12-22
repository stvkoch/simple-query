<?php
namespace Simple;

use Lead\Inflector\Inflector;

class Model
{

    use Traits\ModelProprieties;
    use Traits\ModelMethods;


    public function __construct($options = [])
    {
        if (isset($options['table'])) {
            $this->table = $options['table'];
            $this->alias = $options['table'];
        }
        if (isset($options['alias']) && $options['alias']) {
            $this->alias = $options['alias'];
        }
        $this->pk .= ucfirst(Inflector::singularize($this->table));
        if (isset($options['pk']) && $options['pk']) {
            $this->pk = $options['pk'];
        }
    }


    public function query($field = null) {
      $query = new \Simple\Query($this);
      if (!is_null($field)) {
        $query->select($this->field($field));
      }
      return $query;
    }
}
