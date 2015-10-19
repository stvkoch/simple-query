<?php
include '../src/Simple/Model.php';
include '../src/Simple/Query.php';


class testSqlBuilder extends PHPUnit_Framework_TestCase
{
    public function testSqlDelete()
    {
        $model = new \Simple\Model(['table'=>'superA']);

        $query = (new \Simple\Query($model))->where($model->pk(), 1);

        $sql = 'DELETE FROM superA WHERE id = (?)';
        $this->assertEquals($sql, $query->sqlDelete());

        $query->where($model->field('user'), 2);
        $sql = 'DELETE FROM superA WHERE id = (?) AND superA.user = (?)';
        $this->assertEquals($sql, $query->sqlDelete());

        $this->assertEquals(array(1,2), $query->bindParameters);
    }
}

