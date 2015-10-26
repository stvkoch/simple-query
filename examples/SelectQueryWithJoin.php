<?php
/**
 *
 *
 *
 *
 */
namespace Query\Interpreter;

class User
{

    public function interprete($request)
    {
        $modelUser = \Simple\Factory::model('user', 'U');
        $query = \Simple\Factory::query($modelUser);
        $query->select($modelUser->field('*'));

        $this->onlyCustomer($query, $request);
        $this->idUser($query, $request);

        return $query;
    }

    protected function onlyCustomer($query, $request)
    {
        if (isset($request['onlyCustomer'])) {
            $modelOrder = \Simple\Factory::model('order', 'O');
            $queryJoin = \Simple\Factory::queryJoin($modelUser, $modelOrder);
            $query->join('left', $queryJoin);
            $query->where($modelOrder->field($modelOrder->pk()), 'NULL', 'IS NOT');
        }
    }

    protected function idUser($query, $request)
    {
        if (isset($request['idUser'])) {
            $query->where(
                $modelUser->field($modelUser->pk()),
                $request['idUser']
            );
        }
    }
}

// ...

$interpreter = new \Query\Interpreter\User();
$query = $interpreter->interprete($_REQUEST);


$connection = \Your\Awesome\Connection::get(\Your\Awesome\Connection::READ_ONLY);
$stmt = $connection->prepare($query->sqlSelect());
$result = $query->bind($stmt)->execute();
