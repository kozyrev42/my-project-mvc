<?php

require '../vendor/autoload.php';

use DI\ContainerBuilder;        // подключение контейнера для контроля над зависимостями
use League\Plates\Engine;
use Delight\Auth\Auth;
use Aura\SqlQuery\QueryFactory;

$containerBuilder = new ContainerBuilder();    // создание Объекта DI

// добавляем Исключения, для DI-контейнера
$containerBuilder->addDefinitions([    // если DI-контейнер - увидит в __construct(Engine), 
    Engine::class => function () {      // то он создаст Экземпляр по прописанному правилу:
        return new Engine('../app/views','php');
    },

    PDO::class => function () {
        return new PDO("mysql:host=127.0.0.1;dbname=my-project-mvc;charset=utf8", "root", "");
    },

    Auth::class => function ($container) {
        return new Auth($container->get('PDO'));    // Auth - требует PDO, поэтому обращаемся к Контейнеру DI, с помощью метода ->get, получаем PDO
    },

    QueryFactory::class => function () {
        return new QueryFactory('mysql',null);
    }
]);

$container = $containerBuilder->build();       // сборка умного контейнера из объекта методом build(), для дальнейшего использования


$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {    // записываем в диспетчер пути(роуты), которые будут доступны в приложении, припереходе по Роуту, передаются данные указанные в параметре
    
    // роут на страницу Всех Пользователей
    $r->addRoute('GET', '/', ['App\controllers\HomeController','index']); //HomeController - класс, index - метод Класса
    
    // роут на страницу регистрации
    $r->addRoute('GET', '/page_register', ['App\controllers\RegisterController','page_register']);
    // роут на контроллер регистрации
    $r->addRoute('POST', '/register', ['App\controllers\RegisterController','register']);

    // роут на страницу логирования
    $r->addRoute('GET', '/page_login', ['App\controllers\LoginController','page_login']);
    // роут на контроллер логирования
    $r->addRoute('POST', '/login', ['App\controllers\LoginController','login']);

    // роут на контроллер выхода
    $r->addRoute('GET', '/logout', ['App\controllers\LoginController','logout']);

    // роут на страницу создания юзера
    $r->addRoute('GET', '/page_create_user', ['App\controllers\HomeController','showPageCreate']);
    // роут контроллер создания юзера
    $r->addRoute('POST', '/create_user', ['App\controllers\HomeController','createUser']);

    // страница редактирования профиля
    $r->addRoute('GET', '/page_edit', ['App\controllers\HomeController','showPageEdit']);
    // контроллер редактирования
    $r->addRoute('POST', '/edit', ['App\controllers\HomeController','editUser']);

    // роут на страницу Профиля
    $r->addRoute('GET', '/page_profile', ['App\controllers\HomeController','showPageProfile']);

    // роут на страницу Безопасность
    $r->addRoute('GET', '/page_security', ['App\controllers\HomeController','showPageSecurity']);
    // обработчик страницы Безопасность
    $r->addRoute('POST', '/security', ['App\controllers\HomeController','security']);

    // страница статусы
    $r->addRoute('GET', '/page_status', ['App\controllers\HomeController','showPageStatus']);
    // контроллер редактирования статуса
    $r->addRoute('POST', '/status', ['App\controllers\HomeController','status']);


    // страница аватар
    $r->addRoute('GET', '/page_media', ['App\controllers\HomeController','mediaShow']);
    // обработчик загрузки
    $r->addRoute('POST', '/media_handler', ['App\controllers\HomeController', 'mediaHandler']);


    // удаление пользователя
    $r->addRoute('GET', '/user_delete', ['App\controllers\HomeController', 'delete']);
});


// код из документации, не требует корректировки
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);
$routeInfo = $dispatcher->dispatch($httpMethod, $uri);


// обработка пути из URL
switch ($routeInfo[0]) {    // по умолчание $routeInfo[0]
    case FastRoute\Dispatcher::NOT_FOUND:   // условие для выполнение кейса - подтягивание констант из FastRoute\Dispatcher,  NOT_FOUND - константа содержит "0"
        // кейс в котором - $routeInfo[0] - такой страницы не существует 
        echo '404 страницы не существует';
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:      // METHOD_NOT_ALLOWED - константа содержит "2"
        $allowedMethods = $routeInfo[1];
        echo ' 405 Роут вызван не правильным методом';
        break;
    case FastRoute\Dispatcher::FOUND:       // FOUND - константа содержит "1"
        // $routeInfo[1]; $routeInfo[2]; - приходит информация из параметров вызванного Роута
        $handler = $routeInfo[1];       // получение "название" обработчика, который прописан в диспетчере simpleDispatcher() {...}, Третий параметр из addRoute(1,2,3)
        $vars = $routeInfo[2];          // параметры которые пришли с запросом, их можно использовать
        //d($handler);
        //d($routeInfo[2]);exit;
        // если путь в диспетчере существует, вызван нужным методом, и передана имя контроллера =>
        // => можем вызвать контроллер(функцию) 
        // => передаём контроллеру запрос из адресной строки
        
        // создание Экземпляра прям здесь
        //$controller = new $handler[0];    // $controller = new App\controllers\RegisterController;
        // вызывает функцию по имени которое ей передали, и передаёт ей параметры
        //call_user_func([$controller,$handler[1]],$vars); //  - вызывается $controller и на лету вызывает метод $handler[1] / 'register', передавая методу параметры $vars
    
        // используем  DI контейнер
        $container->call($handler, $vars);
    break;
}