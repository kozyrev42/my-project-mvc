<?php
namespace App\controllers;

use League\Plates\Engine;
use PDO;
use Delight\Auth\Auth;
use \Tamtamchik\SimpleFlash\Flash;

class LoginController
{
    private $templates;
    private $auth;
    //public $flash; 

    public function __construct()
    {
        // создаём Экземпляр Видов, для дальнейшего использования его методов
        $this->templates = new Engine('../app/views','php'); // передаём путь до моих Видов в views

        // Экземпляр подключение к базе
        $db = new PDO("mysql:host=127.0.0.1;dbname=my-project-mvc;charset=utf8", "root", "");

        // создание Экземпляра, передача ему (подключение к базе), далее он подкючен к базе, им можно пользоваться
        $this->auth = new Auth($db,null,null,null);

        // Экземпляр для Flash-сообщений
        $this->flash = new Flash();

    }

    // рендер страницы логирования
    public function page_login()
    {
        echo $this->templates->render('page_login');
    }
    
    // метод логирования
    public function login()
    {
        try {
            //$auth->login($_POST['email'], $_POST['password']);
            $this->auth->login($_POST['email'], $_POST['password']);

            // если залогинюсь перехожу на Главную
            header('Location: /');
        }

        catch (\Delight\Auth\InvalidEmailException $e) {
            //die('Wrong email address');
            $this->flash->message('Не верный электронный адрес','error');
            header('Location: /page_login');
        }
        catch (\Delight\Auth\InvalidPasswordException $e) {
            //die('Wrong password');
            $this->flash->message('Не верный пароль','error');
            header('Location: /page_login');
        }
        catch (\Delight\Auth\EmailNotVerifiedException $e) {
            die('Email not verified');
        }
        catch (\Delight\Auth\TooManyRequestsException $e) {
            die('Too many requests');
        }

    }

}