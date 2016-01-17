<?php
include_once __DIR__. '/../src/Simple/Traits/ModelProprieties.php';
include_once __DIR__. '/../src/Simple/Traits/ModelMethods.php';
include_once __DIR__.'/../src/Simple/Model.php';
include_once __DIR__.'/../src/Simple/Query.php';



class testSqlSelectTest extends PHPUnit_Framework_TestCase
{
    public function testSqlSimpleSelect()
    {
        $modelA = new \Simple\Model(['table'=>'superA']);
        $incrementalQuery = 'SELECT superA.* FROM superA';
        $query = new \Simple\Query($modelA);
        $query->select($modelA->field('*'));
        $this->assertEquals($incrementalQuery, $query->sqlSelect());
    }

    public function testSqlSimpleInSelect()
    {
        $modelA = new \Simple\Model(['table'=>'superA']);
        $incrementalQuery = 'SELECT superA.* FROM superA WHERE superA.test IN (?, ?, ?)';
        $query = new \Simple\Query($modelA);
        $query->select($modelA->field('*'));
        $query->where($modelA->field('test'), array('test_1', 'test_2', 'test_3'));
        $this->assertEquals($incrementalQuery, $query->sqlSelect());
        $this->assertEquals(array('test_1', 'test_2', 'test_3'), $query->bindParameters);
    }

    public function testSqlWhereSelect()
    {
        $modelA = new \Simple\Model(['table'=>'superA']);

        $query = new \Simple\Query($modelA);
        $query->select($modelA->field('*'));
        $query->where($modelA->field('name'), 'joh%', 'LIKE');

        $incrementalQuery = 'SELECT superA.* FROM superA WHERE superA.name LIKE (?)';
        $this->assertEquals($incrementalQuery, $query->sqlSelect());



        $this->assertEquals( 'SELECT COUNT(*) AS count FROM (SELECT superA.id FROM superA WHERE superA.name LIKE (?)) AS _countersuperA', $query->sqlCountSelect());


        $incrementalQuery = 'SELECT superA.* FROM superA WHERE superA.name LIKE (?) OR superA.surename LIKE (?)';
        $query->where($modelA->field('surename'), 'helo%', 'LIKE', 'OR');
        $this->assertEquals($incrementalQuery, $query->sqlSelect());


        $incrementalQuery .= ' HAVING superA.age = (?)';
        $query->having($modelA->field('age'),22);
        $this->assertEquals( 'SELECT COUNT(*) AS count FROM ('.$incrementalQuery.') AS _countersuperA', $query->sqlCountSelect());
    }

    public function testSqlSelectWithAlias()
    {
        $modelA = new \Simple\Model(['table'=>'superA', 'alias'=>'A']);
        $modelB = new \Simple\Model(['table'=>'hiperB', 'alias'=>'B']);
        $modelC = new \Simple\Model(['table'=>'megaC',  'alias'=>'C']);

        $incrementalQuery = 'SELECT A.* FROM superA AS A';

        $query = new \Simple\Query($modelA);
        $query->select($modelA->field('*'));
        $this->assertEquals($incrementalQuery, $query->sqlSelect());

        $queryPage = clone $query;
        $queryPage->page(1,15);
        $this->assertEquals('SELECT A.* FROM superA AS A LIMIT 0, 15', $queryPage->sqlSelect());

        $queryNull = clone $query;

        $queryNull->where($modelA->field('user'), 'NULL');
        $this->assertEquals('SELECT A.* FROM superA AS A WHERE A.user IS NULL', $queryNull->sqlSelect());

        $queryNull->where($modelA->field('gender'), 'NULL', 'IS NOT');
        $this->assertEquals('SELECT A.* FROM superA AS A WHERE A.user IS NULL AND A.gender IS NOT NULL', $queryNull->sqlSelect());

        $queryPage->limit(0,150);
        $this->assertEquals('SELECT A.* FROM superA AS A LIMIT 0, 150', $queryPage->sqlSelect());
        
        $this->assertEquals('SELECT COUNT(*) AS count FROM (SELECT A.id FROM superA AS A) AS _countersuperA', $queryPage->sqlCountSelect());


        $incrementalQuery .= ' WHERE A.name = (?)';
        $query->where($modelA->field('name'), 'John');
        $this->assertEquals($incrementalQuery, $query->sqlSelect());
        $this->assertContains('John', $query->bindParameters);



        $incrementalQuery .= ' AND A.age = (?)';
        $query->where($modelA->field('age'), 1);
        $this->assertEquals($incrementalQuery, $query->sqlSelect());
        $this->assertContains('John', $query->bindParameters);
        $this->assertContains(1, $query->bindParameters);

        $query->group();
        $this->assertEquals('SELECT A.* FROM superA AS A WHERE A.name = (?) AND A.age = (?) GROUP BY A.id',$query->sqlSelect());

        $queryJoin = clone $query;
        $queryJoin->join('left', $modelB);
        $this->assertEquals('SELECT A.* FROM superA AS A LEFT JOIN hiperB AS B ON A.id = B.idSuperA WHERE A.name = (?) AND A.age = (?) GROUP BY A.id',$queryJoin->sqlSelect());

        $queryOrder = clone $queryJoin;
        $queryOrder->order($modelB->field($modelB->pk()));
        $this->assertEquals('SELECT A.* FROM superA AS A LEFT JOIN hiperB AS B ON A.id = B.idSuperA WHERE A.name = (?) AND A.age = (?) GROUP BY A.id ORDER BY B.id ASC', $queryOrder->sqlSelect());

        $queryJoin->join('left',
            (new \Simple\Query($modelC))->equal(
                $modelA->field($modelA->pk()),
                $modelC->field('fk_id_table_A')
            )->where($modelC->field('category'), 2)
        );
        $this->assertEquals('SELECT A.* FROM superA AS A LEFT JOIN hiperB AS B ON A.id = B.idSuperA LEFT JOIN megaC AS C ON A.id = C.fk_id_table_A AND C.category = (?) WHERE A.name = (?) AND A.age = (?) GROUP BY A.id',$queryJoin->sqlSelect());
        $this->assertContains('John', $queryJoin->bindParameters);
        $this->assertContains(1, $queryJoin->bindParameters);
        $this->assertContains(2, $queryJoin->bindParameters);
        $this->assertEquals(array(2,'John',1), $queryJoin->bindParameters);
        $this->assertEquals('SELECT COUNT(*) AS count FROM (SELECT A.id FROM superA AS A LEFT JOIN hiperB AS B ON A.id = B.idSuperA LEFT JOIN megaC AS C ON A.id = C.fk_id_table_A AND C.category = (?) WHERE A.name = (?) AND A.age = (?) GROUP BY A.id) AS _countersuperA', $queryJoin->sqlCountSelect());

        //$this->assertEquals('SELECT A.* FROM superA AS A LEFT JOIN hiperB AS B ON A.id = B.idSuperA LEFT JOIN megaC AS C ON A.id = C.fk_id_table_A WHERE A.name = (?) AND A.age = (?) GROUP BY A.id',$queryJoin->sqlSelect());

        //$this->assertEquals('SELECT A.* FROM superA AS A LEFT JOIN hiperB AS B ON A.id=B.idSuperA',$query->sqlSelect());
    }


    public function testSubQueries()
    {
        $modelA = new \Simple\Model(['table'=>'superA', 'alias'=>'A']);
        $modelB = new \Simple\Model(['table'=>'log',    'alias'=>'B']);
        $modelC = new \Simple\Model(['table'=>'megaC',  'alias'=>'C']);

        $expectSql = 'SELECT A.* FROM superA AS A WHERE id IN (SELECT B.idSuperA FROM log AS B WHERE date BETWEEN (?) AND (?))';

        $query = new \Simple\Query($modelA);
        $query->select($modelA->field('*'));

        $queryLogModelA = (new \Simple\Query($modelB))
                ->select($modelB->fk($modelA))
                ->where('date BETWEEN (?) AND (?)', [1, 2], 'RAW');

        $query->where('id', $queryLogModelA, 'IN');
        $this->assertEquals($expectSql, $query->sqlSelect());
        $this->assertEquals(array(1,2), $query->bindParameters);

    }

    public function testLimit()
    {
        $modelA = new \Simple\Model(['table'=>'superA', 'alias'=>'A']);
        $incrementalQuery = 'SELECT A.* FROM superA AS A LIMIT 123';

        $query = new \Simple\Query($modelA);
        $query->select($modelA->field('*'))->limit(123);
        $this->assertEquals($incrementalQuery, $query->sqlSelect());

        $this->assertEquals($incrementalQuery, $query->sqlSelect());

    }


}

