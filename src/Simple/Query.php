<?php
namespace Simple;

class Query
{
    public $bornTobe = 'select';
    public $from = null;
    public $fields = [];
    public $joins = [];
    public $whereConditions = [];
    public $havingConditions = [];
    public $arrangements = [];
    public $bindParameters = [];
    public $limit = [];
    public $types = [
        'default' => \PDO::PARAM_STR,
        'integer' => \PDO::PARAM_INT,
        'double'  => \PDO::PARAM_INT,
        'null'    => \PDO::PARAM_NULL
    ];

    /**
     * Create instance of query using optional model
     * @param \Simple\Model $model ($optional)
     */
    public function __construct($model = null, $bornToBe = 'select')
    {
        $this->from = $model;
        $this->bornToBe = strtolower($bornToBe);
    }


    /*
    * call apropriete method to build sql that born to be
    */
    public function sql()
    {
        if ($this->bornToBe === 'select') {
            return $this->sqlSelect();
        }
        if ($this->bornToBe === 'count') {
            return $this->sqlCount();
        }
        if ($this->bornToBe === 'update') {
            return $this->sqlUpdate();
        }
        if ($this->bornToBe === 'insert') {
            return $this->sqlInsert();
        }
        if ($this->bornToBe === 'delete') {
            return $this->sqlDelete();
        }
    }

    /**
     * set main model used by query
     *
     * @param null $model
     *
     * @return $this
     */
    public function from($model = null)
    {
        if (is_string($model)) {
            $model = new \Simple\Model(['table'=>$model]);
        }
        $this->from = $model;
        return $this;
    }

    /**
     * Alias of from with semantic of INSERT
     *
     * @param null $model
     *
     * @return Query
     */
    public function into($model = null) {
        return $this->from($model);
    }

    /**
     * Add query field used on select, insert and update query type
     * In case of SELECT
     *      $selectQuery->field('*')->from('user')->sqlSelect();
     *  //SELECT * FROM user
     *
     *      $insertQuery->field('name', 'Jonh')->field('age', 22)->into(user')->sqlInsert();
     * // INSERT INTO user (name, age) VALUES (?, ?);
     *
     *      $updateQuery->from('user')->field('age', 23)->where('name', 'Jonh')->sqlUpdate();
     * // UPDATE user SET (age = ?) WHERE name = ?
     *
     * @param $field
     * @param null $value
     *
     * @return $this
     */
    public function field($field, $value = null)
    {
        $this->fields[$field] = $value;
        return $this;
    }


    /**
     * Alias of field method with semantic of SELECT SQL
     * @example
     *  // get all items from $model
     *  $query->select($model->field('*'));
     *
     * @param $field
     *
     * @return Query
     */
    public function select($field)
    {
        return $this->field($field);
    }


    /**
     * Add query WHERE condition
     * @example
     *  $query->where($model->pk(), $id2, '=', 'OR');
     *  $query->where($model->pk(), $id1, '=', 'OR');
     *
     * @param $field
     * @param $value
     * @param string $function
     * @param string $operator
     *
     * @return $this
     */
    public function where($field, $value, $function = '=', $operator = 'AND')
    {
        $this->whereConditions[strtoupper($operator)][strtoupper($function)][] = ['field'=>$field, 'value'=>$value];
        return $this;
    }

    /**
     * Add query HAVING condition
     * @example
     *  $query->where($model->pk(), $id2, '=', 'OR');
     *  $query->where($model->pk(), $id1, '=', 'OR');
     *
     * @param $field
     * @param $value
     * @param string $function
     * @param string $operator
     *
     * @return $this
     */
    public function having($field, $value, $function = '=', $operator = 'AND')
    {
        $this->havingConditions[strtoupper($operator)][strtoupper($function)][] = ['field'=>$field, 'value'=>$value];
        return $this;
    }


    /**
     * Add literal condition without pass by statement
     *  $query->equal('1','1')
     *
     * @param $field
     * @param $value
     * @param string $function
     * @param string $operator
     *
     * @return $this
     */
    public function equal($field, $value, $function = '=', $operator = 'AND')
    {
        $this->whereConditions[strtoupper($operator)][strtoupper($function)][] = [$field, $value];
        return $this;
    }


    /**
     * Add join condition
     * @example
     *  $modelA = ...;
     *  $modelB = ...;
     *  $query = new \Simple\Query($model);
     *  $query->join('left', $modelB);
     *  $query->select($modelA->field('*'))->select($modelB->field('name'));
     * ....
     *
     * @param $type
     * @param $model
     * @param null $query
     *
     * @return $this
     */
    public function join($type, $model, $query = null)
    {
        if ($model instanceof Model and is_null($query)) {
            $query = new self();
            $query->equal($this->from->field($this->from->pk()), $model->fk($this->from));
            $query->from($model);
        }

        if ($model instanceof self) {
            $query = $model;
        }

        $this->joins[strtoupper($type)][] = $query;
        return $this;
    }


    /**
     * Add order, allow multiples orders
     * @example
     *  $query->order($modelA->field('created_at'), 'DESC');
     *
     * @param null $field
     * @param string $order
     *
     * @return $this
     */
    public function order($field = null, $order = 'ASC')
    {
        if (is_null($field)) {
            $this->arrangements['ORDER BY'][$this->from->field($this->from->pk())] = $order;
            return $this;
        }

        $this->arrangements['ORDER BY'][$field] = $order;
        return $this;
    }


    /**
     * Add group statement
     * @example
     *  $query->group($model->pk());
     *
     * @return $this
     */
    public function group()
    {
        if (func_num_args()===0) {
            $field = $this->from->field($this->from->pk());
            $this->arrangements['GROUP BY'][$field] = $field;
            return $this;
        }
        $field = implode(', ', func_get_args());
        $this->arrangements['GROUP BY'][$field] = $field;
        return $this;
    }


    /**
     * Helper calculate limit and offset by page and perPage argument
     *
     * @param $page
     * @param $perPage
     *
     * @return $this
     */
    public function page($page, $perPage)
    {
        if ($page < 1) {
            $page = 1;
        }
        $this->arrangements['LIMIT'] = [((int)$page-1)*$perPage, $perPage];
        return $this;
    }


    /**
     * Set limit of sql
     *
     * @return $this
     */
    public function limit()
    {
         $this->arrangements['LIMIT'] = func_get_args();
        return $this;
    }

    /**
     * Helper that bind parameters of this query inside of connection statements
     * $query->where('name', $name);
     * $query->where('age', $age);
     *
     * $stmt = $connection->prepare($query->sqlSelect());
     * $result = $query->bind($stmt)->execute();
     *
     * @param $stmt
     *
     * @return mixed
     */
    public function bind(\PDOStatement $stmt)
    {
        foreach ($this->bindParameters as $count => $value) {
            $stmt->bindParam(
                $count+1,
                $value,
                $this->type($value)
            );
        }

        return $stmt;
    }


    /**
     * Helper function that return mysql bind_param type of value
     *  i is for integer
     *  d is for double
     *  s is for string
     *
     * @param $value
     *
     * @return mixed
     */
    public function type($value)
    {
        $type = gettype($value);

        if (isset($this->types[$type])) {
            return $this->types[$type];
        }

        return $this->types['default'];
    }


    /**
     * Generate SQL count follow conditions of query
     * This SQL ignore pagination, limit values, order and any unnecessary
     * statement
     *
     * @param string $fieldName
     *
     * @return string
     */
    public function sqlCountSelect($fieldName = 'count')
    {
        $this->initMaker();
        $select = trim(
            sprintf(
                'SELECT %s FROM %s%s%s%s%s%s',
                # if has having condition use fields
                $this->hasHavingConditions() ?
                    implode(', ', array_keys($this->fields))
                    : $this->from->field($this->from->pk()),
                $this->makeTable($this->from),
                $this->makeAlias($this->from),
                $this->makeJoins(),
                $this->makeConditions('WHERE'),
                $this->makeGroup(),
                $this->makeConditions('HAVING', $this->havingConditions)
            )
        );
        return trim(
            sprintf(
                'SELECT COUNT(*) AS %s FROM (%s) AS _counter%s',
                $fieldName,
                $select,
                $this->makeTable($this->from)
            )
        );
    }

    /**
     * Generate Select SQL based on conditions and statements values
     *
     * @return string
     */
    public function sqlSelect()
    {
        $this->initMaker();
        return trim(
            sprintf(
                'SELECT %s FROM %s%s%s%s%s%s%s%s',
                implode(', ', array_keys($this->fields)),
                $this->makeTable($this->from),
                $this->makeAlias($this->from),
                $this->makeJoins(),
                $this->makeConditions('WHERE'),
                $this->makeGroup(),
                $this->makeConditions('HAVING', $this->havingConditions),
                $this->makeOrder(),
                $this->makeLimit()
            )
        );
    }

    public function sqlDelete()
    {
        $this->initMaker();
        return trim(
            sprintf(
                'DELETE FROM %s%s%s',
                $this->makeTable($this->from),
                $this->makeJoins(),
                $this->makeConditions('WHERE')
            )
        );
    }


    public function sqlUpdate()
    {
        $this->initMaker();
        return trim(
            sprintf(
                'UPDATE %sSET (%s) %s',
                $this->makeTable($this->from),
                $this->makeFields(),
                $this->makeConditions('WHERE')
            )
        );
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
        return implode(', ', array_fill(1, count($this->fields), '(?)'));
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
        if (isset($this->arrangements['ORDER BY'])) {
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
        if (isset($this->arrangements['GROUP BY'])) {
            return 'GROUP BY '. implode(', ', $this->arrangements['GROUP BY']).' ';
        }
        return '';
    }


    public function makeLimit()
    {
        if (isset($this->arrangements['LIMIT'])) {
            return 'LIMIT '. implode(', ', $this->arrangements['LIMIT']);
        }
        return '';
    }


    /**
     * makeConditions
     * @param string $initial
     * @param null $initialConditions
     *
     * @return string
     */
    public function makeConditions($initial = '', $initialConditions = null)
    {
        // default use condition, but you can pass havingCondition instead
        if (is_null($initialConditions)) {
            $initialConditions = $this->whereConditions;
        }

        $conds = [];

        foreach ($initialConditions as $oper => $functions) {
            $partCondition = [];
            foreach ($functions as $func => $conditions) {
                foreach ($conditions as $condition) {

                    if (!(isset($condition['field']) && isset($condition['value']))) {
                        $partCondition[] = sprintf('%s %s %s', $condition[0], $func, $condition[1]);
                        continue;
                    }

                    if (is_null($condition['value']) || $condition['value'] === 'NULL') {
                        if ($func === '=') {
                            $func = 'IS';
                        }
                        $partCondition[] = sprintf('%s %s NULL', $condition['field'], $func);
                        continue;
                    }

                    if ($func === 'RAW') {
                        $partCondition[] = $condition['field'];
                        $this->bindParameters = array_merge($this->bindParameters, $condition['value']);
                        continue;
                    }

                    if ($condition['value'] instanceof self) {
                        $partCondition[] = sprintf('%s %s (%s)', $condition['field'], $func, $condition['value']->sqlSelect());
                        $this->bindParameters = array_merge($this->bindParameters, $condition['value']->bindParameters);
                        continue;
                    }

                    if (is_array($condition['value'])) {
                        $placeHolder = implode(', ', array_fill(0, count($condition['value']), '?'));
                        $partCondition[] = sprintf('%s IN (%s)', $condition['field'], $placeHolder);
                        $this->bindParameters = array_merge($this->bindParameters, $condition['value']);
                        continue;
                    }

                    $partCondition[] = sprintf('%s %s (?)', $condition['field'], $func);
                    $this->bindParameters[] = $condition['value'];
                }
            }
            $conds[] = implode(' '.$oper.' ', $partCondition);
        }

        if (count($conds)) {
            return sprintf('%s %s ', $initial, implode(' OR ', $conds));
        }

        return '';
    }

    public function makeTable($model)
    {
        if ($model instanceof Model) {
            return $model->table().' ';
        }
        return $model.' ';
    }

    public function makeAlias($model)
    {
        if ($model instanceof Model && $model->alias() !== $model->table()) {
            return 'AS ' .$model->alias(). ' ';
        }
        return '';
    }

    protected function makeJoins()
    {
        $_joins = [];
        $bindParameters = [];
        foreach ($this->joins as $type => $joins) {
            foreach ($joins as $joinQuery) {
                $_joins[] = sprintf(
                    '%s JOIN %s%s%s',
                    $type,
                    $joinQuery->makeTable($joinQuery->from),
                    $joinQuery->makeAlias($joinQuery->from),
                    $joinQuery->makeConditions('ON')
                );

                $this->bindParameters = array_merge($this->bindParameters, $joinQuery->bindParameters);
            }
        }

        if (count($_joins)) {
            return implode('', $_joins);
        }

        return '';
    }

    /**
     * @return bool
     */
    protected function hasHavingConditions()
    {
        return (boolean) count($this->havingConditions);
    }
    
    public function __toString()
    {
        return call_user_func([$this, 'sql'.$this->bornToBe]);
    }

}
