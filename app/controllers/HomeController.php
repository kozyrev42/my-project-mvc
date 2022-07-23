<?php
namespace App\controllers;

use App\models\QueryBuilderUsers;
use League\Plates\Engine;
use Delight\Auth\Auth;
use \Tamtamchik\SimpleFlash\Flash;

class HomeController
{
    private $templates;
    private $auth;
    public $dbQB;


    public function __construct(Engine $templates, QueryBuilderUsers $dbQB, Auth $auth, Flash $flash)
    {
        // создаём Экземпляр видов, для дальнейшего использования его методов
        //$this->templates = new Engine('../app/views','php'); // передаём путь до моих Видов в views
        $this->templates = $templates;

        // Экземпляр подключение к базе
        //$db = new PDO("mysql:host=127.0.0.1;dbname=my-project-mvc;charset=utf8", "root", "");
        // PDO - создан в конфигурации DI-контейнера

        // создание Экземпляра, передача ему (подключение к базе), далее он подкючен к базе, им можно пользоваться
        $this->auth = $auth;

        // Экземпляр работает с запросами к Базе
        //$this->dbQB = new QueryBuilderUsers();
        // создаём Экземпляр с помощью DI-контейнера, Экземпляр  
        $this->dbQB = $dbQB;

        // Экземпляр для Flash-сообщений
        $this->flash = $flash;
    }

    public function index() // если залогинин
    {   
        if ($this->auth->isLoggedIn()) {
            // получение всех из таблицы 'users'
            $users = $this->dbQB->getAll('users');
            echo $this->templates->render('page_users', ['usersInView' => $users, 'auth' => $this->auth]); //в Вид передаём: результат вызова из базы, объект $this->auth
        } else {
            header('Location: /page_login');    // иначе на страницу Логирования
        }
    }

    // метод по рендеру страницы создания пользователя
    public function showPageCreate()
    {
        if(!$this->auth->hasRole(\Delight\Auth\Role::ADMIN)){
            $this->flash->warning('Не Админ, отказано в доступе');
            header('Location: /');
            exit;
        }

        if ($this->auth->isLoggedIn() || $this->auth->isRemembered()){
            if ($this->auth->hasRole(\Delight\Auth\Role::ADMIN)) {
                // рендерим страницу из Видов
                echo $this->templates->render('page_create_user', ['auth' => $this->auth]);
                exit;
            }else {
                $this->flash->warning('зашел, но Не Админ, отказано в доступе');
                header('Location: /');
                exit;
            }
        } else {
            $this->flash->warning('Позователь не авторизован');
            header('Location: /page_login');
        }
    }

    // метод обработчик создания Пользователя
    public function createUser()
    {
        try {
            $userId = $this->auth->register($_POST['email'], $_POST['password']);

            //если userId создан, то обновляем данные для созданной записи
            if ($userId){

                //обновляем данные в таблице users
                $this->dbQB->updateInfo('users',
                    [
                        'username' => $_POST['username'],   // 'поле в таблице' => $_POST['input name="..."'],
                        'position' => $_POST['position'],
                        'tel' => $_POST['tel'],
                        'address' => $_POST['address'],
                        'status_color' => $_POST['status_select'],
                        'vk' => $_POST['vk'],
                        'teleg' => $_POST['teleg'],
                        'insta' => $_POST['insta'],
                    ],
                    $userId
                    );

            }


            $this->flash->success('Мы зарегистрировали нового пользователя с ID ' . $userId);
            header('Location: /');
            exit;
        }
        catch (\Delight\Auth\InvalidEmailException $e) {
            $this->flash->warning('Invalid email address');
        }
        catch (\Delight\Auth\InvalidPasswordException $e) {
            $this->flash->warning('Invalid password');
        }
        catch (\Delight\Auth\UserAlreadyExistsException $e) {
            $this->flash->warning('Пользователь уже существует');
        }
        catch (\Delight\Auth\TooManyRequestsException $e) {
            $this->flash->warning('Too many requests');
        }

        // если регистрация не удалась, снова рендерим страницу создания, данные из формы не удалятся
        echo $this->templates->render('page_create_user', ['auth' => $this->auth, 'name' => $_POST['name'], 'job_title' => $_POST['job_title'], 'phone' => $_POST['phone'], 'address' => $_POST['address'], 'status' => $_POST['status'], 'vk' => $_POST['vk'], 'telegram' => $_POST['telegram'], 'instagram' => $_POST['instagram'], 'email' => $_POST['email']]);
    }

    // страница Редактирования пользователя
    // public function editShowForm($id)       // $id - сюда прилитит из Роутинга
    // {
    //     // проверка, свой-ли профиль открыл или админ
    //     // if(!($this->auth->getUserId() == $id || $this->auth->hasRole(\Delight\Auth\Role::ADMIN))){
    //     //     $this->flash->warning('Access denied');
    //     //     header('Location: /');
    //     //     exit;
    //     // }

    //     // $userInfo = $this->dbQB->getById($id);  // информация о Юзере по id
    //     echo $this->templates->render('page_edit', ['auth' => $this->auth, /* 'user' => $userInfo */]);
    // }

    public function editShow()
    {
    echo $this->templates->render('page_edit');
    }
}