<?php
namespace Simple;

interface QueryInterface {}

class Query implements QueryInterface {
    public $from = null;
    public $fields = [];
    public $joins = [];
    public $conditions = [];
    public $arrangements = [];
    public $bindParameters = [];
    public $limit = [];
    public $types = [
        'default' => 's',
        'integer' => 'i',
        'double'  => 'd'
    ];

    public function __construct($model= null)
    {
        $this->from = $model;
    }

    public function from($model = null)
    {
        $this->from = $model;
        return $this;
    }

    public function field($field, $value = null)
    {
        $this->fields[$field] = $value;
        return $this;
    }


    public function select($field)
    {
        return $this->field($field);
    }


    public function where($field, $value, $function = '=', $operator = 'AND')
    {
        $this->conditions[strtoupper($operator)][strtoupper($function)][] = ['field'=>$field, 'value'=>$value];
        return $this;
    }


    public function equal($field, $value, $function = '=', $operator = 'AND')
    {
        $this->conditions[strtoupper($operator)][strtoupper($function)][] = [$field, $value];
        return $this;
    }


    public function join($type, $model, $query = null)
    {
        if ($model instanceof Model and is_null($query) ){
            $query = new self();
            $query->equal($this->from->field($this->from->pk()), $model->fk($this->from));
            $query->from($model);
        }

        if ( $model instanceof self ) {
            $query = $model;
        }

        $this->joins[strtoupper($type)][] = $query;
        return $this;
    }


    public function order($field = null, $order = 'ASC')
    {
        if(is_null($field)) {
            $this->arrangements['ORDER BY'][$this->from->field($this->from->pk())] = $order;
            return $this;
        }

        $this->arrangements['ORDER BY'][$field] = $order;
        return $this;
    }


    public function group()
    {
        if(func_num_args()===0) {
            $this->arrangements['GROUP BY'][] = $this->from->field($this->from->pk());
            return $this;
        }
        $this->arrangements['GROUP BY'][] = implode(', ', func_get_args());
        return $this;
    }


    public function page($page, $perPage)
    {
        if ($page < 1) {
            $page = 1;
        }
        $this->arrangements['LIMIT'] = [((int)$page-1)*$perPage, $perPage];
    }


    public function limit($limit, $offset)
    {
         $this->arrangements['LIMIT'] = [$limit, $offset];
    }

    public function type($value)
    {
        $type = gettype($value);

        if (isset($this->types[$type])) {
            return $this->types[$type];
        }
        return $this->type['default'];
    }

    public function sqlCountSelect()
    {
        $this->initMaker();
        return trim(sprintf('SELECT COUNT(*) AS count FROM (%s) AS _counter%s',
                trim(sprintf('SELECT %s FROM %s%s%s%s%s')
                    implode(', ', array_keys($this->fields)),
                    $this->makeTable($this->from),
                    $this->makeAlias($this->from),
                    $this->makeJoins(),
                    $this->makeConditions('WHERE'),
                    $this->makeGroup()
                )),
                $this->makeTable($this->from)
        ));
    }
    
    public function sqlSelect()
    {
        $this->initMaker();
        return trim(sprintf(
            'SELECT %s FROM %s%s%s%s%s%s%s',
                implode(', ', array_keys($this->fields)),
                $this->makeTable($this->from),
                $this->makeAlias($this->from),
                $this->makeJoins(),
                $this->makeConditions('WHERE'),
                $this->makeGroup(),
                $this->makeOrder(),
                $this->makeLimit()
        ));
    }

    public function sqlDelete()
    {
        $this->initMaker();
        return trim(sprintf(
            'DELETE FROM %s%s%s',
                $this->makeTable($this->from),
                $this->makeJoins(),
                $this->makeConditions('WHERE')
        ));
    }


    public function sqlUpdate()
    {
        $this->initMaker();
        return trim(sprintf(
            'UPDATE %sSET (%s) %s',
                $this->makeTable($this->from),
                $this->makeFields(),
                $this->makeConditions('WHERE')
        ));
    }


    public function sqlInsert()
    {
        $this->initMaker();
        return trim(sprintf(
            'INSERT %s(%s) VALUES (%s)',
            $this->makeTable($this->from),
            implode(', ', array_keys($this->fields)),
            $this->makeValuePlaceHolders()
        ));
    }


    public function initMaker()
    {
        $this->bindParameters = [];
        if (count($this->fields)===0) {
            $this->field($this->from->field('*'));
        }
    }


    public function makeValuePlaceHolders()
    {
        $this->bindParameters = array_values($this->fields);
        return implode(', ', array_fill(1,count($this->fields), '(?)'));
    }


    public function makeFields()
    {
        $fields = [];
        foreach ($this->fields as $field => $value) {
            $fields[] = $field . ' = (?)';
            $this->bindParameters[] = $value;
        }
        return implode(', ', $fields);
    }


    public function makeOrder()
    {
        if(isset($this->arrangements['ORDER BY'])) {
            $orders = [];
            foreach ($this->arrangements['ORDER BY'] as $field => $order) {
                $orders[] = sprintf('%s %s', $field, $order);
            }
            return 'ORDER BY '. implode(', ', $orders).' ';
        }
        return '';
    }


    public function makeGroup()
    {
        if(isset($this->arrangements['GROUP BY'])) {
            return 'GROUP BY '. implode(', ', $this->arrangements['GROUP BY']).' ';
        }
        return '';
    }


    public function makeLimit()
    {
        if(isset($this->arrangements['LIMIT'])) {
            return 'LIMIT '. implode(', ', $this->arrangements['LIMIT']);
        }
        return '';
    }


    public function makeConditions($initial = '')
    {
        $conds = [];
        $bindParameters = [];

        foreach ($this->conditions as $oper => $functions) {
            $partCondition = [];
            foreach ($functions as $func => $conditions) {
                foreach ($conditions as $condition) {
                    if (!(isset($condition['field']) && isset($condition['value']))) {
                        $partCondition[] = sprintf('%s %s %s', $condition[0], $func, $condition[1]);
                        continue;
                    }
                    if ( is_null($condition['value']) || $condition['value'] === 'NULL') {
                        if ($func === '=')
                            $func = 'IS';
                        $partCondition[] = sprintf('%s %s NULL', $condition['field'], $func );
                        continue;
                    }
                    $partCondition[] = sprintf('%s %s (?)', $condition['field'], $func );
                    $this->bindParameters[] = $condition['value'];
                }
            }
            $conds[] = implode(' '.$oper.' ', $partCondition);
        }

        if (count($conds)) {
            return sprintf('%s %s ', $initial, implode( ' OR ', $conds));
        }

        return '';
    }

    public function makeTable($model)
    {
        if($model instanceof Model) {
            return $model->table().' ';
        }
        return $model.' ';
    }

    public function makeAlias($model)
    {
        if($model instanceof Model && $model->alias() !== $model->table()) {
            return 'AS ' .$model->alias(). ' ';
        }
        return '';
    }

    public function makeJoins()
    {
        $_joins = [];
        $bindParameters = [];
        foreach ($this->joins as $type => $joins) {
            foreach( $joins as $joinQuery)
            {
                $_joins[] = sprintf('%s JOIN %s%s%s',
                    $type,
                    $joinQuery->makeTable($joinQuery->from),
                    $joinQuery->makeAlias($joinQuery->from),
                    $joinQuery->makeConditions('ON'));

                $this->bindParameters = array_merge($this->bindParameters, $joinQuery->bindParameters);
            }
        }
        if(count($_joins))
            return implode('', $_joins);

        return '';
    }
}
