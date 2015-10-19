<?php
include '../src/Simple/Model.php';
include '../src/Simple/Query.php';


class testSqlBuilder extends PHPUnit_Framework_TestCase
{
    public function testSqlUpdate()
    {
        $model = new \Simple\Model(['table'=>'superA']);

        $query = (new \Simple\Query($model))
            ->field($model->field('username'), 'John')
            ->field($model->field('password'), 'hello_world')
            ->where($model->pk(), 1);

        $sql = 'UPDATE superA SET (superA.username = (?), superA.password = (?)) WHERE id = (?)';
        $this->assertEquals($sql, $query->sqlUpdate());

        $this->assertEquals(array('John', 'hello_world', 1), $query->bindParameters);
    }
}

