<?php
namespace App\controllers;

use App\QueryBuilder;
use League\Plates\Engine;

class HomeController
{
    private $templates;
    public function __construct()
    {
        // создаём Экземпляр видов, для дальнейшего использования его методов
        $this->templates = new Engine('../app/views','php'); // передаём путь до моих Видов в views
    }

    public function register()
    {
        // рендер шаблона из вида
        echo $this->templates->render('page_register');
    }

    public function page_login()
    {
        // рендер шаблона из вида
        echo $this->templates->render('page_login');
    }

    
}