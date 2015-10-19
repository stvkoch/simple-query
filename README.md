# Simple SQL Builder

![Travis-ci](https://travis-ci.org/stvkoch/simple-query.svg)


## See on examples:

### Simple SELECT query

    $model = \Simple\Model(['table'=>'superA', 'alias'=>'A']);
    $query = (new \Simple\Query($model))->where($model->field('username'), 'superUser');

    echo $query->sqlSelect();
    // SELECT A.* FROM superA AS A WHERE A.username = (?)

    # use 'bindParameters' propriety to bind parameters in your database statement
    $query->bindParameters
    # ['superUser']


    # more real example
    $supossed_mysql_stmt = $con->prepare($query->sqlSelect());
    # you can bind_param this way
    foreach ($query->bindParameters as $value) {
        $supossed_mysql_stmt->bind_param(
            $query->type($value),#return 'i', 'd' or 's'
            $value
        );
    }
    ...

## About Model

Model allow to separate table structures to avoid conflicts when you want to create more complex queries.

### Constructor

    $model = new \Simple\Model([
        'table' => 'nameOfTable',
        'alias' => 'shortName',
        'pk' => 'id' ## default value
    ]);

### pk()

Primary key of model

    #example
    $model = new \Simple\Model([
        'table'=>'superA',
        'alias'=>'A'
    ]);
    echo $model->pk();
    # A.id



### fk(\Simple\Model $modelB)

How key from model $modelB are represented inside of Model like a foreing key. Useful in joins


    #example
    $modelA = new \Simple\Model([
        'table'=>'superA',
        'alias'=>'A'
    ]);
    $modelA = new \Simple\Model([
        'table'=>'megaB',
        'alias'=>'B'
    ]);
    echo $modelA->fk($modelB);
    # A.idMegaB



### field($fieldName)

Useful method return anti-collision field name

    $modelA = new \Simple\Model([
        'table'=>'superA',
        'alias'=>'A'
    ]);
    echo $model->field('name');
    # A.name


### table()

Return table name


### alias()

return alias table



## Now We Can Build Queries!!

    namespace My\Model;
    class User extends \Simple\Model {
        protected $table = 'user';
        protected $alias = 'us';
    }

    $userModel = new \My\Model\User();
    $query = new \Simple\Query($userModel);
    $query->where($userModel->field('username'), $usernameParameter);

    $stmt = $mysql_con->prepare($query->sqlSelect());
    ....


### Multiples conditions

    # example A
    $userModel = new \Simple\Model(['table'=>'users']);
    $query = new \Simple\Query($userModel);
    $query->where($userModel->field('username'), $value);
    $query->where($userModel->field('password'), $hashPass);

    # example B
    $userModel = new \Simple\Model(['table'=>'users']);
    $query = new \Simple\Query($userModel);
    $query->where($userModel->field('name'), 'John%', 'LIKE');
    # OR condition
    $query->where($userModel->field('surename'), 'John%', 'LIKE', 'OR');
    ...

    # query equal method don't use bind parameter. equal method inject value inside of query. and work in the same way of where
    $query->equal($model->field('status'), 1);


    # IS NULL
    $query->where($model->field('age'), 'NULL', 'IS');

    # IS NOT NULL
    $query->where($model->field('age'), 'NULL', 'IS NOT');



### Group

    $query->group($model->pk());


### Order

    $query->order($model->field('name'), 'DESC');

### Joins

    #example A
    $modelA = new ModelA();
    $modelB = new ModelB();
    $query = new \Simple\Query($modelA);
    $query->join('left', $modelB);

    #example A.2. Allow join queries
    $modelA = new ModelA();
    $modelB = new ModelB();
    $query = new \Simple\Query($modelA);
    $query->join('left',
        (new \Simple\Query($modelB))
            ->equal($modelA->pk(), $modelA->fk($modelB))
            ->where('name', $paramName)
    );

### Pagination or Limit

    # calcule limit and offset basead on page and perPage values
    $query->page($page, $perPage);

    # or you can use limit and offset directly
    $query->limit(0, 10);



### Bind Parameters

When you work with values Simple Query Build allways work with SQL statement.

You can bind your values this way:


    # more real example
    $supossed_mysql_stmt = $con->prepare($query->sqlSelect());
    # you can bind_param this way
    foreach ($query->bindParameters as $value) {
        $supossed_mysql_stmt->bind_param(
            $query->type($value),#return 'i', 'd' or 's'
            $value
        );
    }
