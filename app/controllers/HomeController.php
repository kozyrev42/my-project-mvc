<?php
namespace App\controllers;

//use App\QueryBuilder;
use League\Plates\Engine;
use PDO;
use Delight\Auth\Auth;

class HomeController
{
    private $templates;
    private $auth;

    public function __construct()
    {
    // создаём Экземпляр видов, для дальнейшего использования его методов
    $this->templates = new Engine('../app/views','php'); // передаём путь до моих Видов в views

    // Экземпляр подключение к базе
    $db = new PDO("mysql:host=127.0.0.1;dbname=my-project-mvc;charset=utf8", "root", "");

    // создание Экземпляра, передача ему (подключение к базе), далее он подкючен к базе, им можно пользоваться
    $this->auth = new Auth($db,null,null,null);
    }


    public function page_login()
    {
        // рендер шаблона логирования
        echo $this->templates->render('page_login');
    }

    public function page_users()
    {
        // рендер шаблона логирования
        echo $this->templates->render('page_users');
    }

}