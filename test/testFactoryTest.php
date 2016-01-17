<?php
namespace Test;

include_once __DIR__. '/../src/Simple/Traits/ModelProprieties.php';
include_once __DIR__. '/../src/Simple/Traits/ModelMethods.php';
include_once __DIR__.'/../src/Simple/Model.php';
include_once __DIR__.'/../src/Simple/Query.php';
include_once __DIR__.'/../src/Simple/Factory.php';
include_once __DIR__.'/../vendor/autoload.php';

class TestFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryModel()
    {
        $modelA = \Simple\Factory::model('superA');
        $incrementalQuery = 'SELECT superA.* FROM superA';
        $query = new \Simple\Query($modelA);
        $query->select($modelA->field('*'));
        $this->assertEquals($incrementalQuery, $query->sqlSelect());
    }

    public function testQuerySelect()
    {
        $modelA = \Simple\Factory::model('superA');
        $query = \Simple\Factory::query($modelA);


        $query->select($modelA->field('*'));
        $query->where($modelA->field('name'), 'joh%', 'LIKE');
        $incrementalQuery = 'SELECT superA.* FROM superA WHERE superA.name LIKE (?)';
        $this->assertEquals($incrementalQuery, $query->sqlSelect());
        $this->assertEquals('SELECT COUNT(*) AS count FROM (SELECT superA.idSuperA FROM superA WHERE superA.name LIKE (?)) AS _countersuperA', $query->sqlCountSelect());
        $incrementalQuery = 'SELECT superA.* FROM superA WHERE superA.name LIKE (?) OR superA.surename LIKE (?)';
        $query->where($modelA->field('surename'), 'helo%', 'LIKE', 'OR');
        $this->assertEquals($incrementalQuery, $query->sqlSelect());
    }

    public function testQueryJoinWithModel()
    {
        $modelA = \Simple\Factory::model('superA', 'A');
        $modelB = \Simple\Factory::model('hiperB', 'B');

        $queryJoin = \Simple\Factory::queryJoin($modelA, $modelB);
        $queryJoin->where($modelB->field('name'), 'john');

        $query = \Simple\Factory::query($modelA);
        $query->select($modelA->field('*'));


        $query->join('left', $queryJoin);
        $this->assertEquals('SELECT A.* FROM superA AS A LEFT JOIN hiperB AS B ON A.idSuperA = B.idSuperA AND B.name = (?)', $query->sqlSelect());
    }



    public function testQueryJoinWithArray()
    {
        $modelA = \Simple\Factory::model('superA', 'A');
        $modelB = \Simple\Factory::model('hiperB', 'B');

        $queryJoin = \Simple\Factory::queryJoin($modelA, array('hiperB', 'B'));
        $queryJoin->where($modelB->field('name'), 'john');

        $query = \Simple\Factory::query(array('superA', 'A'));
        $query->select($modelA->field('*'));

        $query->join('left', $queryJoin);
        $this->assertEquals('SELECT A.* FROM superA AS A LEFT JOIN hiperB AS B ON A.idSuperA = B.idSuperA AND B.name = (?)', $query->sqlSelect());
    }
}
