<?php
namespace App\models;

use Aura\SqlQuery\QueryFactory;    // подключение пространства имён с классом из него
use PDO;    // подключаем встроенный в PHP namespace, для использования его в нутри Классов

class QueryBuilderUsers
{
    private $pdo;
    private $queryFactory;

    public function __construct()
    {
        $this->pdo = new PDO("mysql:host=127.0.0.1;dbname=my-project-mvc;charset=utf8", "root", "");
        $this->queryFactory = new QueryFactory('mysql',null);  // создание Экземпляра класса, подключенного из vendor
    }

    public function getAll($table)
    {
        $select = $this->queryFactory->newSelect();  // Создайте запрос Select, далее будем им пользоваться
        $select->cols(['*'])     // Чтобы добавить столбцы в выборку, используйте метод cols().'*'
            ->from($table);    // по цепочке, вызываем следующий метод

        //var_dump($select->getStatement()); // получаем, готовый sql-запрос: "SELECT * FROM `email_list`"
        
        $sth = $this->pdo->prepare($select->getStatement()); // подготавливаем запрос
        $sth->execute($select->getBindValues());             // выполняем запрос
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function insert($table,$data)
    {
        $insert = $this->queryFactory->newInsert();     // Создайте запрос insert, далее будем им пользоваться

        $insert
            ->into($table)                   // INTO this table
            ->cols($data);                      // bind values as "(col) VALUES (:col)"
            //var_dump($insert->getStatement());exit;
            $sth = $this->pdo->prepare($insert->getStatement());    // подготавливаем запрос
            $sth->execute($insert->getBindValues());                // выполняем запрос
    }


    public function update($table,$email,$data)
    {
        $update = $this->queryFactory->newUpdate();
        $update
            ->table($table)                  
            ->cols( $data)
            ->where('email = :email')
            ->bindValue('email', $email);
            //var_dump($update->getStatement());exit;
            $sth = $this->pdo->prepare($update->getStatement());   
            $sth->execute($update->getBindValues());
    }

    public function delete($table,$email)
    {
        $delete = $this->queryFactory->newDelete();
        $delete
            ->from($table)                  
            ->where('email = :email')
            ->bindValue('email', $email);
            //var_dump($delete->getStatement());exit;
            $sth = $this->pdo->prepare($delete->getStatement());   
            $sth->execute($delete->getBindValues());
    }
}
