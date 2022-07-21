<?php
namespace App\controllers;
if( !session_id() ) @session_start();   // старт сессии, если сессия не открыта

use League\Plates\Engine;
use PDO;
use Delight\Auth\Auth;
use \Tamtamchik\SimpleFlash\Flash;

class RegisterController
{
    private $templates;
    private $selector;
    private $token;
    private $auth;

    public function __construct(Engine $templates, Auth $auth, Flash $flash)
    {
        // создаём Экземпляр видов, для дальнейшего использования его методов
        //$this->templates = new Engine('../app/views','php'); // передаём путь до моих Видов в views
        $this->templates = $templates;

        // Экземпляр подключение к базе
        //$db = new PDO("mysql:host=127.0.0.1;dbname=my-project-mvc;charset=utf8", "root", "");
        // PDO - создан в конфигурации DI-контейнера

        // создание Экземпляра, передача ему (подключение к базе), далее он подкючен к базе, им можно пользоваться
        //$this->auth = new Auth($db,null,null,null);   // без di-контейнера
        $this->auth = $auth; 

        // Экземпляр для Flash-сообщений
        //$this->flash = new Flash();
        $this->flash = $flash;
    }

    // рендеринг шаблона
    public function page_register()
    {
        echo $this->templates->render('page_register');
    }

    // метод регистрации
    public function register()
    {
        try {
            $userId = $this->auth->register($_POST['email'], $_POST['password'], $_POST['email'], function ($selector, $token) {
                // $userId = $auth->register($_POST['email'], $_POST['password'], $_POST['username'], function ($selector, $token) {
                //echo 'Send ' . $selector . ' and ' . $token . ' to the user (e.g. via email)';
                $this->selector = $selector;
                $this->token = $token;
            });
            
            // если ->register() - выполнится, записываем сообщение об успешной регистрации
            $this->flash->message('Регистрация успешна, можете войти!','success');

            // рендер шаблона из видов     
            header('Location: /page_login');
        }

        // отлов ошибок Исключений
        catch (\Delight\Auth\InvalidEmailException $e) {
            die('Invalid email address');
        }
        catch (\Delight\Auth\InvalidPasswordException $e) {
            die('Invalid password');
        }
        catch (\Delight\Auth\UserAlreadyExistsException $e) {
            //die('User already exists');
            $this->flash->message('Пользователь уже существует!','error');
            header('Location: /page_register');
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
    }
}