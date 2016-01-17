<?php
namespace Simple;

abstract class Factory
{
    /**
     * @usage
     *  \Simple\Factory::getModel(array('Tablename', 'Alias'));
     */
    public static function getModel($model)
    {
        if ($model instanceof Model) {
            return $model;
        }
        return call_user_func_array('self::model', $model);
    }

    /**
     * manufactore and return a model
     */
    public static function model($table, $alias = null, $pk = null)
    {
        $model = new Model([
            'table' => $table
        ]);
        if ($alias) {
            $model->alias($alias);
        }
        if ($pk) {
            $model->pk($pk);
        }
        return $model;
    }

    /**
     * Manufactory for you and
     * return a query from model.
     */
    public static function query($model, $alias = null, $pk = null)
    {
        $model = self::getModel($model, $alias, $pk);
        $query = new Query($model);
        return $query;
    }

    /**
     * Example, get all user are customer.
     *
     * $queryJoin = \Simple\Factory::queryJoin($modelUser, $modelOrder);
     * $queryJoin->where($modelOrder->field($modelOrder->pk()), 'NULL', 'IS NOT');
     *
     * $query = \Simple\Factory::query($modelUser);
     * $query->join('left', $queryJoin);
     * ...
     */
    public static function queryJoin($modelA, $modelB)
    {
        $modelA = self::getModel($modelA);
        $modelB = self::getModel($modelB);

        $query = self::query($modelB);
        $query->equal(
            $modelA->field($modelA->pk()),
            $modelB->fk($modelA)
        );
        return $query;
    }
}
