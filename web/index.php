<?php
/**
 * 159.339 Internet Programming 2017.2
 * Student ID: 15146508
 * Assignment: 2   Date: 30/09/17
 * System: PHP 7.1
 * Code guidelines: PSR-1, PSR-2
 *
 * FRONT CONTROLLER - Responsible for URL routing and User Authentication
 *
 * @package cgwatkin/a2
 * @author  Cai Gwatkin <caigwatkin@gmail.com>
 **/
date_default_timezone_set('Pacific/Auckland');

require __DIR__ . '/vendor/autoload.php';

use PHPRouter\RouteCollection;
use PHPRouter\Router;
use PHPRouter\Route;

define('APP_ROOT', __DIR__);
define('DB_HOST', 'mysql');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'cgwatkin_a2');

$collection = new RouteCollection();

$collection->attachRoute(
    new Route(
        '/account', array(
            '_controller' => 'cgwatkin\a2\controller\AccountController::indexAction',
            'methods' => 'GET',
            'name' => 'accountIndex'
        )
    )
);

$collection->attachRoute(
    new Route(
        '/account', array(
            '_controller' => 'cgwatkin\a2\controller\AccountController::loginAction',
            'methods' => 'POST',
            'name' => 'accountLogin'
        )
    )
);

$collection->attachRoute(
    new Route(
        '/account/list/', array(
        '_controller' => 'cgwatkin\a2\controller\AccountController::listAction',
        'methods' => 'GET',
        'name' => 'accountList'
        )
    )
);

$collection->attachRoute(
    new Route(
        '/account/create/', array(
        '_controller' => 'cgwatkin\a2\controller\AccountController::createAction',
        'methods' => 'GET',
        'name' => 'accountCreate'
        )
    )
);

$collection->attachRoute(
    new Route(
        '/account/delete/:id', array(
        '_controller' => 'cgwatkin\a2\controller\AccountController::deleteAction',
        'methods' => 'GET',
        'name' => 'accountDelete'
        )
    )
);

$collection->attachRoute(
    new Route(
        '/account/update/:id', array(
        '_controller' => 'cgwatkin\a2\controller\AccountController::updateAction',
        'methods' => 'GET',
        'name' => 'accountUpdate'
        )
    )
);

$router = new Router($collection);
$router->setBasePath('/');

$route = $router->matchCurrentRequest();

// If route was dispatched successfully - return
if ($route) {
    // true indicates to webserver that the route was successfully served
    return true;
}

// Otherwise check if the request is for a static resource
$info = parse_url($_SERVER['REQUEST_URI']);
// check if its allowed static resource type and that the file exists
if (preg_match('/\.(?:png|jpg|jpeg|css|js)$/', "$info[path]")
    && file_exists("./$info[path]")
) {
    // false indicates to web server that the route is for a static file - fetch it and return to client
    return false;
} else {
    header("HTTP/1.0 404 Not Found");
    // Custom error page
    // require 'static/html/404.html';
    return true;
}
