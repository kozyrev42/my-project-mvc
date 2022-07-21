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

    public function __construct(Engine $templates, Auth $auth, Flash $flash)
    {
        // создаём Экземпляр Видов, для дальнейшего использования его методов
        //$this->templates = new Engine('../app/views','php'); // передаём путь до моих Видов в views   // без di-контейнера
        $this->templates = $templates;

        // Экземпляр подключение к базе
        //$db = new PDO("mysql:host=127.0.0.1;dbname=my-project-mvc;charset=utf8", "root", "");
        // PDO - создан в конфигурации DI-контейнера

        // создание Экземпляра, передача ему (подключение к базе), далее он подкючен к базе, им можно пользоваться
        //$this->auth = new Auth($db,null,null,null);   // без di-контейнера
        $this->auth = $auth;

        // Экземпляр для Flash-сообщений
        //$this->flash = new Flash();   // без di-контейнера
        $this->flash = $flash;
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
            $this->auth->login($_POST['email'], $_POST['password']);

            // если залогинен переход на Главную
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

    // выход из приложения
    public function logout()
    {
        try {
            $this->auth->logOut();
            $this->flash->message('Пользователь вышел из системы','warning');
            header('Location: /');
        }
        catch (\Delight\Auth\NotLoggedInException $e) {
            $this->flash->message('Неизвестная ошибка','warning');
            header('Location: /');
        }
    }
}
