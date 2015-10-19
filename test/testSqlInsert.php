<?php
include '../src/Simple/Model.php';
include '../src/Simple/Query.php';


class testSqlBuilder extends PHPUnit_Framework_TestCase
{
    public function testSqlInsert()
    {
        $model = new \Simple\Model(['table'=>'superA']);

        $query = (new \Simple\Query($model))
            ->field($model->field('username'), 'John')
            ->field($model->field('password'), 'hello_world');

        $sql = 'INSERT superA (superA.username, superA.password) VALUES ((?), (?))';

        $this->assertEquals($sql, $query->sqlInsert());

        $this->assertEquals(array('John', 'hello_world'), $query->bindParameters);
    }
}
