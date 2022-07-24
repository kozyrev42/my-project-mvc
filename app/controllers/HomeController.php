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
        // проверка на логирование
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
    public function showPageEdit()
    {
        $id = $_GET['id']; // id - редактируемого пользователя
        
        //проверка, свой-ли профиль открыл или админ
        if(!($this->auth->getUserId() == $id || $this->auth->hasRole(\Delight\Auth\Role::ADMIN))){
            $this->flash->warning('Access denied');
            header('Location: /');
            exit;
        }

        $userInfo = $this->dbQB->getById($id);  // информация о Юзере по id
        //d($userInfo);exit;

        echo $this->templates->render('page_edit', ['auth' => $this->auth, 'user' => $userInfo]);
    }

    // обработчик страницы редактирования
    public function editUser()
    {
        // echo "editUser";
        // d($_POST);
        $userId = $_POST['id'];
        $this->dbQB->updateInfo('users',
                    [
                        'username' => $_POST['username'],   // 'поле в таблице' => $_POST['input name="..."'],
                        'position' => $_POST['position'],
                        'tel' => $_POST['tel'],
                        'address' => $_POST['address'],
                    ],
                    $userId
                    );
        //$userInfo = $this->dbQB->getById($userId);
        $this->flash->success('Информация успешно редактирована!');
        header('Location: /');
    }

    // рендер страницы Профиля
    public function showPageProfile()
    {
        $id = $_GET['id']; // id - редактируемого пользователя
        
        //проверка, свой-ли профиль открыл или админ
        if(!$this->auth->isLoggedIn()){
            $this->flash->warning('Не вошли в приложение!');
            header('Location: /');
            exit;
        }
        $userInfo = $this->dbQB->getById($id);  // информация о Юзере по id
        //d($userInfo);exit;
        echo $this->templates->render('page_profile', ['auth' => $this->auth, 'user' => $userInfo]);
    }

    // рендер страницы Безопасность
    public function showPageSecurity()
    {
        $id = $_GET['id'];
        // проверка на Текущего пользователя и на Админа
        if(!($this->auth->getUserId() == $id || $this->auth->hasRole(\Delight\Auth\Role::ADMIN))){
            $this->flash->warning('Access denied');
            header('Location: /');
            exit;
        }
        // получаем информацию Пользователя
        $userInfo = $this->dbQB->getById($id);
        echo $this->templates->render('page_security', ['auth' => $this->auth, 'user' => $userInfo]);

    }

    // обработчик страницы Безопасности
    public function security()
    {       
        $id = $_POST['id'];
        $userInfo = $this->dbQB->getById($id);
        
        $newPassword = $_POST['newPassword'];
        // изменение Пароля
        try {
            $this->auth->changePasswordWithoutOldPassword($newPassword);

            $this->flash->success('Пароль успешно изменён!');
        }
        catch (\Delight\Auth\NotLoggedInException $e) {
            $this->flash->warning('Not logged in');
        }
        catch (\Delight\Auth\InvalidPasswordException $e) {
            $this->flash->warning('Invalid password(s)');
        }
        catch (\Delight\Auth\TooManyRequestsException $e) {
            $this->flash->warning('Too many requests');
        }

        // изменение почты
        /* ? */

        //header('Location: /page_security');
        echo $this->templates->render('page_security', ['auth' => $this->auth, 'user' => $userInfo ]);
    }

    
    // страница Статусы пользователя 
    public function showPageStatus()
    {
        $id = $_GET['id']; // id - редактируемого пользователя
        
        //проверка, свой-ли профиль открыл или админ
        if(!($this->auth->getUserId() == $id || $this->auth->hasRole(\Delight\Auth\Role::ADMIN))){
            $this->flash->warning('Access denied');
            header('Location: /');
            exit;
        }

        $userInfo = $this->dbQB->getById($id);  // информация о Юзере по id

        echo $this->templates->render('page_status', ['auth' => $this->auth, 'user' => $userInfo]);
    }

    // обработчик страницы Статусы
    public function status()
    {
        $userId = $_POST['id'];
        $this->dbQB->updateInfo('users',
                    [
                        'status_color' => $_POST['status_select'] // 'поле в таблице' => $_POST['input name="..."'],
                    ],
                    $userId
                    );
        //$userInfo = $this->dbQB->getById($userId);
        $this->flash->success('Информация успешно редактирована!');
        header('Location: /');
    }

    public function mediaShow()
    {
        $id = $_GET['id'];
        if(!($this->auth->getUserId() == $id || $this->auth->hasRole(\Delight\Auth\Role::ADMIN))){
            $this->flash->warning('Access denied');
            header('Location: /');
            exit;
        }

        $userInfo = $this->dbQB->getById($id);  // информация о Юзере по id

        echo $this->templates->render('page_media', ['auth' => $this->auth, 'user' => $userInfo]);
    }




    public function mediaHandler()
    {
        $userId = $_POST['id'];
        $userInfo = $this->dbQB->getById($userId);  // информация о Юзере по id

        // данные по файлу
        $image_name = $_FILES['image']['name'];
        $tmp_name = $_FILES['image']['tmp_name'];
        // если аватар не выбран, выход из функции
        if (empty($image_name)) {
            $this->flash->warning('файл не выбран');
            header('Location: /');
            exit;
        }

        // если аватар выбран
        if (!empty($image_name)) {
            //d($image_name);

            // есть ли аватар в базе
            $name_avatar = $userInfo['avatar'];

            // аватар в базе ЕСТЬ
            if (!empty($name_avatar)) {
                // удаление файла из каталога 
                @unlink("img/demo/avatars/" . $name_avatar);

                // загрузка новой
                // получим расширение файла
                $extension = pathinfo($image_name)["extension"];
                // формируем уникальное имя файла
                $uniq_image_name = uniqid() . "." . $extension;

                // сохранить картинку в постоянную папку
                // формируем путь сохранения, откуда
                $tmp_name = $_FILES['image']['tmp_name'];
                //куда
                $target = "img/demo/avatars/" . $uniq_image_name;
                // перемещаем в постоянную папку
                move_uploaded_file($tmp_name, $target);

                // записать в базу имени загруженего файла
                $this->dbQB->updateInfo('users',
                    [
                        'avatar' => $uniq_image_name // 'поле в таблице' => $_POST['input name="..."'],
                    ],
                    $userId
                    );
                $this->flash->success('Аватар обновлён!');
                header('Location: /');
                exit;
            }

            // аватара в базе НЕТ, загружаем картинку, обновляем базу
            if (!$name_avatar) {
                // получим расширение файла
                $extension = pathinfo($image_name)["extension"];
                // формируем уникальное имя файла
                $uniq_image_name = uniqid() . "." . $extension;

                // сохранить картинку в постоянную папку
                // формируем путь сохранения, откуда
                $tmp_name = $_FILES['image']['tmp_name'];
                //куда
                $target = "img/demo/avatars/" . $uniq_image_name;
                // перемещаем в постоянную папку
                move_uploaded_file($tmp_name, $target);

                // записать в базу имени загруженего файла
                $this->dbQB->updateInfo('users',
                    [
                        'avatar' => $uniq_image_name // 'поле в таблице' => $_POST['input name="..."'],
                    ],
                    $userId
                    );
                $this->flash->success('Аватар обновлён!');
                header('Location: /');
                exit;
            }
        }
    }




    public function delete()
    {
        $userId = $_GET['id'];

        // проверка, авторизован или админ?
        if(!($this->auth->getUserId() == $userId || $this->auth->hasRole(\Delight\Auth\Role::ADMIN))){
            $this->flash->warning('Access denied');
            header('Location: /');
            exit;
        }

        $this->dbQB->delete($userId);

        if($this->auth->hasRole(\Delight\Auth\Role::ADMIN)){
            $this->flash->success('User deleted');
            header('Location: /');
            exit;
        } else {
            $this->auth->logOut();
            $this->flash->success('User deleted');
            header('Location: /login');
            exit;
        }
    }
}