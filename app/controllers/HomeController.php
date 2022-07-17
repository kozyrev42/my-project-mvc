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
    public $dbqb;

    public function __construct()
    {
    // создаём Экземпляр видов, для дальнейшего использования его методов
    $this->templates = new Engine('../app/views','php'); // передаём путь до моих Видов в views

    // Экземпляр подключение к базе
    $db = new PDO("mysql:host=127.0.0.1;dbname=my-project-mvc;charset=utf8", "root", "");

    // создание Экземпляра, передача ему (подключение к базе), далее он подкючен к базе, им можно пользоваться
    $this->auth = new Auth($db,null,null,null);

    // 
    $this->dbqb = new QueryBuilderUsers();
    }

    public function index()
    {   
        if ($this->auth->isLoggedIn()) {
            // объект подключения к базе
            //$db = new QueryBuilder();
            $posts = $this->dbqb->getAll('users');
            // Render a template
            //echo $this->templates->render('homepage', ['postsInView' => $posts]); // рендерим страницу Пользователей, в вид передаём результат вызова из базы ['posts' => $posts]

            echo $this->templates->render('page_users', ['postsInView' => $posts]);    // если залогинин, рендерим страницу Пользователей
        } else {
            header('Location: /page_login');    // иначе на страницу Логирования
        }
    }
}