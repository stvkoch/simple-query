<?php
namespace Test;

include_once __DIR__. '/../src/Simple/Traits/ModelProprieties.php';
include_once __DIR__. '/../src/Simple/Traits/ModelMethods.php';
include_once __DIR__.'/../src/Simple/Model.php';
include_once __DIR__.'/../src/Simple/Query.php';

class TestSqlHashCodeTest extends \PHPUnit_Framework_TestCase
{
    public function testSqlHashCodeSelect()
    {
        $modelA = new \Simple\Model(['table'=>'superA']);

        $queryA = new \Simple\Query($modelA);
        $queryA->select($modelA->field('*'));
        $queryA->where($modelA->field('name'), 'John');
        $queryA->where($modelA->field('age'), 22);

        $queryB = new \Simple\Query($modelA);
        $queryB->where($modelA->field('age'), 22);
        $queryB->where($modelA->field('name'), 'John');
        $queryB->select($modelA->field('*'));

        $this->assertEquals(
            $queryA->hashCode('select'),
            $queryB->hashCode('select')
        );

        $queryB->where($modelA->field('surename'), 'Hello');

        $this->assertNotEquals(
            $queryA->hashCode('select'),
            $queryB->hashCode('select')
        );
    }
}
