<?php
namespace App\controllers;

use App\models\QueryBuilderUsers;
use League\Plates\Engine;
use PDO;
use Delight\Auth\Auth;

class HomeController
{
    private $templates;
    private $auth;
    public $dbQB;

    public function __construct()
    {
        // создаём Экземпляр видов, для дальнейшего использования его методов
        $this->templates = new Engine('../app/views','php'); // передаём путь до моих Видов в views

        // Экземпляр подключение к базе
        $db = new PDO("mysql:host=127.0.0.1;dbname=my-project-mvc;charset=utf8", "root", "");

        // создание Экземпляра, передача ему (подключение к базе), далее он подкючен к базе, им можно пользоваться
        $this->auth = new Auth($db,null,null,null);

        // Экземпляр работает с запросами к Базе
        $this->dbQB = new QueryBuilderUsers();
    }

    public function index() // если залогинин
    {   
        if ($this->auth->isLoggedIn()) {
            // получение всех из таблицы 'users'
            $posts = $this->dbQB->getAll('users');
            echo $this->templates->render('page_users', ['postsInView' => $posts]); //в вид передаём результат вызова из базы ['postsInView' => $posts]
        } else {
            header('Location: /page_login');    // иначе на страницу Логирования
        }
    }
}