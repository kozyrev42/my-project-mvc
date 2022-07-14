<?php

require '../vendor/autoload.php';


$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {    // записываем в диспетчер пути(роуты), которые будут доступны в приложении, припереходе по Роуту, передаются данные указанные в параметре
    
    // роут на страницу регистрации  ВРЕМЕННО !!!!!
    $r->addRoute('GET', '/', ['App\controllers\HomeController','page_register']); //HomeController - класс, index - метод Класса
    // роут на страницу логирования
    $r->addRoute('GET', '/page_login', ['App\controllers\HomeController','page_login']);
    // роут на страницу регистрации
    $r->addRoute('GET', '/page_register', ['App\controllers\HomeController','page_register']);

    // роут на контроллер регистрации
    $r->addRoute('POST', '/register', ['App\controllers\HomeController','register']);


});


// не обращать внимание, код не требует корректировки
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
        $handler = $routeInfo[1];       // получение "название" обработчика, который прописан в диспетчере 'simpleDispatcher'
        $vars = $routeInfo[2];          // параметры которые пришли с запросом, их можно использовать

        //d($handler); exit; // $handler - содержит Третий параметр из addRoute(1,2,3)
        //d($vars); exit;
        // если путь в диспетчере существует, вызван нужным методом, и передана имя контроллера =>
        // => можем вызвать контроллер(функцию) 
        // => передаём контроллеру запрос из адресной строки

        // вызывает функцию по имени которое ей передали, и передаёт ей параметры
        //call_user_func($handler, $vars);

        // [$handler[0],$handler[1]] - вызывается $handler[0] и на лету вызывает метод $handler[1], передавая методу параметры $vars
        //call_user_func([$handler[0],$handler[1]], $vars);  
        
        // создание Экземпляра прям здесь
        $controller = new $handler[0];          // new App\controllers\HomeController;
        
        // вызов метода созданного Экземпляра
        //$controller->index(1233);

        // вызов метода '$handler[1]]'  Экземпляра

        call_user_func([$controller,$handler[1]],$vars);
break;
}