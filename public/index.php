<?php
/**
 * фронт-контроллер, содержит маршрутизатор
 */
require '../vendor/autoload.php';


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

    // роут на страницу пользователи
    //$r->addRoute('GET', '/page_users', ['App\controllers\HomeController','page_users']);

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
        $handler = $routeInfo[1];       // получение "название" обработчика, который прописан в диспетчере 'simpleDispatcher', Третий параметр из addRoute(1,2,3)
        $vars = $routeInfo[2];          // параметры которые пришли с запросом, их можно использовать
        
        // если путь в диспетчере существует, вызван нужным методом, и передана имя контроллера =>
        // => можем вызвать контроллер(функцию) 
        // => передаём контроллеру запрос из адресной строки

        // создание Экземпляра прям здесь
        $controller = new $handler[0];          // new App\controllers\HomeController;
        // вызывает функцию по имени которое ей передали, и передаёт ей параметры
        call_user_func([$controller,$handler[1]],$vars);    // [$handler[0],$handler[1]] - вызывается $handler[0] и на лету вызывает метод $handler[1], передавая методу параметры $vars
    break;
}