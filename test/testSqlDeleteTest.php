<?php
include_once __DIR__. '/../src/Simple/Traits/ModelProprieties.php';
include_once __DIR__. '/../src/Simple/Traits/ModelMethods.php';
include_once __DIR__.'/../src/Simple/Model.php';
include_once __DIR__.'/../src/Simple/Query.php';
include_once __DIR__.'/../vendor/autoload.php';


class testSqlDeleteTest extends PHPUnit_Framework_TestCase
{
    public function testSqlDelete()
    {
        $model = new \Simple\Model(['table'=>'superA']);

        $query = (new \Simple\Query($model))->where($model->pk(), 1);

        $sql = 'DELETE FROM superA WHERE idSuperA = (?)';
        $this->assertEquals($sql, $query->sqlDelete());

        $query->where($model->field('user'), 2);
        $sql = 'DELETE FROM superA WHERE idSuperA = (?) AND superA.user = (?)';
        $this->assertEquals($sql, $query->sqlDelete());

        $this->assertEquals(array(1,2), $query->bindParameters);
    }
}

