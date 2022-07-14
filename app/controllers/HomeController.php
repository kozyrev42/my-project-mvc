<?php
namespace App\controllers;

use App\QueryBuilder;
use League\Plates\Engine;
use PDO;
use Delight\Auth\Auth;

class HomeController
{
    private $templates;
    private $selector;
    private $token;
    private $auth;

    public function __construct()
    {
        // создаём Экземпляр видов, для дальнейшего использования его методов
        $this->templates = new Engine('../app/views','php'); // передаём путь до моих Видов в views

        // подключение к базе
        $db = new PDO("mysql:host=127.0.0.1;dbname=my-project-mvc;charset=utf8", "root", "");

        // создание объекта, передача ему (подключение к базе), далее он подкючен к базе, им можно пользоваться
        $this->auth = new Auth($db,null,null,null);
        
    }

    public function page_register()
    {
        // рендер шаблона из вида
        echo $this->templates->render('page_register');
    }

    // метод регистрации
    public function register()
    {
        try {
            $userId = $this->auth->register($_POST['email'], $_POST['password'], $_POST['email'], function ($selector, $token) {
                // $userId = $auth->register($_POST['email'], $_POST['password'], $_POST['username'], function ($selector, $token) {
                // $userId = $this->auth->register('dd@dd.dd', 'dd', 'dd', function ($selector, $token) {
                //echo 'Send ' . $selector . ' and ' . $token . ' to the user (e.g. via email)';
                $this->selector = $selector;
                $this->token = $token;
            });
            
            // если ->register() - выполнится, выводим сообщение 
            // echo 'Вы зарегистрировали нового пользователя с идентификатором ' . $userId;
        }
        // отлов ошибок Исключений
        catch (\Delight\Auth\InvalidEmailException $e) {
            die('Invalid email address');
        }
        catch (\Delight\Auth\InvalidPasswordException $e) {
            die('Invalid password');
        }
        catch (\Delight\Auth\UserAlreadyExistsException $e) {
            die('User already exists');
        }
        catch (\Delight\Auth\TooManyRequestsException $e) {
            die('Too many requests');
        }


        // сразу верифицируем
        try {
            // метод получит 'selector' и 'token' из письма юзера, если совпадёт, то верифицирует Юзера в базе
            //$this->auth->confirmEmail($_GET['selector'], $_GET['token']);
            $this->auth->confirmEmail($this->selector, $this->token);

            //echo 'Email address has been verified';
        }
        catch (\Delight\Auth\InvalidSelectorTokenPairException $e) {
            die('Invalid token');
        }
        catch (\Delight\Auth\TokenExpiredException $e) {
            die('Token expired');
        }
        catch (\Delight\Auth\UserAlreadyExistsException $e) {
            die('Email address already exists');
        }
        catch (\Delight\Auth\TooManyRequestsException $e) {
            die('Too many requests');
        }

        // сообщение о успешной регистрации
                
        // рендер шаблона из видов
        echo $this->templates->render('page_register');
    }

    public function page_login()
    {
        // рендер шаблона логирования
        echo $this->templates->render('page_login');
    }
}