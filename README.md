# Simple SQL Builder

![Travis-ci](https://travis-ci.org/stvkoch/simple-query.svg)


- Simple SELECT query
- Model
- Query Methos
- Examples
- Improvements

## See on examples:

### Simple SELECT query

    $model = \Simple\Model(['table'=>'superA', 'alias'=>'A']);

    $query = (new \Simple\Query($model))
        ->where($model->field('username'), 'superUser');

    echo $query->sqlSelect();
    // SELECT A.* FROM superA AS A WHERE A.username = (?)

    // use 'bindParameters' propriety to bind parameters in your database statement
    $query->bindParameters
    // ['superUser']


    // more real example
    $supossed_mysql_stmt = $con->prepare($query->sqlSelect());
    // you can bind_param this way
    foreach ($query->bindParameters as $value) {
        $supossed_mysql_stmt->bind_param(
            $query->type($value), // return 'i', 'd' or 's'
            $value
        );
    }
    ...



## Why we not build onw string sql?

Using SQL Builder you can:

    - increase security, avoid sql injection.
    - create powerful query without specific order.
    - use bind values.
    - create easy subquery conditions using query object.
    - apply patterns to interprete request and build personal queries.
    - not dependent of ORMs.



## About Model

Model allow to separate table structures to avoid conflicts when you want to create more complex queries.

### Constructor

    $model = new \Simple\Model([
        'table' => 'nameOfTable',
        'alias' => 'shortName',
        'pk' => 'id' ## default value
    ]);


### pk()

Primary key of model(table)

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

    $modelB = new \Simple\Model([
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
    echo $modelA->field('name');
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



### Subquery condition


    $modelUser = new \Simple\Model(['table'=>'user']);
    $modelOrder = new \Simple\Model(['table'=>'order']);

    // get all user are active customer in holiday month

    $queryOrder = new \Simple\Query($modelOrder);
    $queryOrder->field($modelOrder->fk($modelUser))
        ->where('createAt BETWEEN (?) AND (?)', ['2015-12-01','2015-12-31'], 'RAW')
        ->group($modelOrder->fk($modelUser));

    $query = new \Simple\Query($modelUser);
    $query->where($modelUser->field($modelUser->pk()), $queryOrder, 'IN')
        ->order($modelUser->field('name'))
        ->page(1, 20);

    +'SELECT user.* FROM user WHERE user.id IN (SELECT order.idUser FROM order WHERE createAt BETWEEN (?) AND (?) GROUP BY order.idUser) ORDER BY user.name ASC LIMIT 0, 20'




### Where

Add condition. This apply to 'having' method

    $query
        ->where($fieldName, $valueToBind, $function = '=', $operator = 'AND')
        ->where(...);

Support conditions functions:

    '=','>','<','<=','>='
    'IS'
    'IS NO'
    'IN'
    'NOT IN'
    'RAW' (not apply function condition, you should provide complete condition)


#### Examples

'=','>','<','<=','>=', '!='

    $query->where('field', $value, '!=');

'IN' or 'NOT IN'

    $query->where('field', array($value1, $value2), 'IN');

'RAW' (not apply function condition, you should provide complete condition)
RAW function is a powerful function condition.

    $query->where('name = (?) AND age = (?) AND actice=1', [$value1, $value2], 'RAW');



### equal

equal method add conditions that is not parsed or encoded by query.
This is a danger feature but necessary when used to add keys tables in joins conditions.


$queryJoin = new \Simple\Query($modelB);
$queryJoin->equal($modelA->pk(), $modelB->fk($modelA));




### Group

    $query->group($model->pk());
    $query->group($model->field('name'));
    ...


### Order

    $query->order($model->field('name'), 'DESC');
    // accept DESC or ASC


### Joins

Joins is another complex structure that \Simple\Query help you made simple and powerful.
With \Simple\Query you can join models and queries objects.
Join table work with keys co-related between tables.
Generally this key are someone primary key and your foreign key (that represent primary key some table)

\Simple\Model have two methods that help you organized keys pk() and fk().


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

    # calc limit and offset based on page and perPage values
    $query->page($page, $perPage);

    # or you can use limit and offset directly
    $query->limit(0, 10);


### Build SQL SELECT

    $query->sqlSelect();

### Build SQL COUNT

    $query->sqlCountSelect();


### Build SQL UPDATE

    $query->sqlUpdate();

### Build SQL INSERT

    $query->sqlInsert();

### Buidl SQL DELETE

    $query->sqlDelete();


### Bind Parameters

When you work with values Simple Query Build allways work with SQL statement.


$stmt = $con->prepare($query->sqlSelect());
$query->bind($stmt)->execute()->get_result();


Or you can bind your values this way:


    # more real example
    $supossed_mysql_stmt = $con->prepare($query->sqlSelect());
    # you can bind_param this way
    foreach ($query->bindParameters as $value) {
        $supossed_mysql_stmt->bind_param(
            $query->type($value),#return 'i', 'd' or 's'
            $value
        );
    }





## Improvements


- Create index of fields used in WHERE, ORDER and JOINs.
- Try agroup your condition fields inside of compose index.
- Use correct type length to store your data. Only use big store datatype when really required!
- Benchmark your queries and index.
- Use EXPLAIN to discovery how and what you need improve.
- Try discovery if are more fast paginate in PHP side. Benchmark this choice!
- Even COUNT queries, benchmark and discovery if are more fast calculate this PHP side.
- Disable log queries in production.


