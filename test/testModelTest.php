<?php
include_once __DIR__. '/../src/Simple/Traits/ModelProprieties.php';
include_once __DIR__. '/../src/Simple/Traits/ModelMethods.php';
include_once __DIR__.'/../src/Simple/Model.php';
include_once __DIR__.'/../src/Simple/Query.php';
include_once __DIR__.'/../vendor/autoload.php';


class testModelTest extends PHPUnit_Framework_TestCase
{

    public function testAlias()
    {
        $model = new \Simple\Model([
            'table'=>'superA',
            'alias'=>'AA'
        ]);
        $this->assertEquals($model->alias(), 'AA');
        $this->assertEquals($model->table(), 'superA');
        $this->assertEquals($model->pk(), 'id');
        $this->assertEquals($model->field('work'), 'AA.work');
    }


    public function testModel()
    {
        $model = new \Simple\Model([
            'table'=>'megaA'
        ]);
        $this->assertEquals($model->alias(), 'megaA');
        $this->assertEquals($model->table(), 'megaA');
        $this->assertEquals($model->pk(), 'id');
        $this->assertEquals($model->field('work'), 'megaA.work');
    }

    public function testPk()
    {
        $model = new \Simple\Model([
            'table'=>'superA',
            'pk'=>'primaryKeyField'
        ]);
        $this->assertEquals($model->pk(), 'primaryKeyField');
        $this->assertEquals($model->field('work'), 'superA.work');
    }


    public function testFk()
    {
        $model = new \Simple\Model([
            'table'=>'superA',
            'pk'=>'primaryA'
        ]);

        $modelB = new \Simple\Model([
            'table'=>'superB',
            'pk'=>'primaryB'
        ]);
        $this->assertEquals($model->pk(), 'primaryA');
        $this->assertEquals($model->field('work'), 'superA.work');
        $this->assertEquals($model->fk($modelB), 'superA.primaryBSuperB');
    }
}

