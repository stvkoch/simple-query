<?php

include '../src/Simple/Model.php';
include '../src/Simple/Query.php';


class testSqlBuilder extends PHPUnit_Framework_TestCase
{

    protected function getRequestA()
    {
        return [
            'user' => 'john.macdonald',
            'pass' => 'xsde4df9'
        ];
    }

    protected function getRequestB()
    {
        return [
            'name' => 'john.mx%',
            'sort' => ['name' => 'ASC']
        ];
    }

    public function shouldSqlFromRequestA()
    {
        return 'SELECT super.* FROM superTableA AS super WHERE super.username = (?) AND super.password = (?)';
    }

    public function shouldSqlFromRequestB()
    {
        return 'SELECT super.* FROM superTableA AS super WHERE super.name LIKE (?) ORDER BY super.name ASC';
    }

    public function testInterpreterA()
    {
        $model = new \Simple\Model([
            'table'=>'superTableA',
            'alias'=>'super'
        ]);
        $query = new \Simple\Query($model);
        $params = $this->getRequestA();

        $this->interpret($query, $params);
        $this->assertEquals($this->shouldSqlFromRequestA(), $query->sqlSelect());
    }

    public function testInterpreterB()
    {
        $model = new \Simple\Model([
            'table'=>'superTableA',
            'alias'=>'super'
        ]);
        $query = new \Simple\Query($model);
        $params = $this->getRequestB();

        $this->interpret($query, $params);
        $this->assertEquals($this->shouldSqlFromRequestB(), $query->sqlSelect());
    }


    protected function interpret(\Simple\Query $query, $params)
    {
        $this->interpreterSortName($query, $params);
        $this->interpreterName($query, $params);
        $this->interpreterCredential($query, $params);
    }


    protected function interpreterSortName(\Simple\Query $query, $params)
    {
        if (isset($params['sort']['name'])) {
            $order = $params['sort']['name']=='DESC' ? 'DESC' : 'ASC';
            $query->order($query->from->field('name'), $params['sort']['name']);
        }
    }

    protected function interpreterName(\Simple\Query $query, $params)
    {
        if (isset($params['name'])) {
            $query->where($query->from->field('name'), $params['name'], 'LIKE');
        }
    }

    protected function interpreterCredential(\Simple\Query $query, $params)
    {
        if (isset($params['user']) && $params['pass']) {
            $query->where($query->from->field('username'), $params['user']);
            $query->where($query->from->field('password'), $params['pass']);
        }
    }

}

