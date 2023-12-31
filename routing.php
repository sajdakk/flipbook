<?php

require_once 'src/controllers/ErrorController.php';
require_once 'src/controllers/SecurityController.php';
require_once 'src/controllers/AddBookController.php';
require_once 'src/controllers/DashboardController.php';
require_once 'src/controllers/AdminController.php';
require_once 'src/controllers/BookDetailsController.php';
require_once 'src/controllers/ProfileController.php';
require_once 'src/controllers/TopController.php';
require_once 'src/controllers/FavoritesController.php';

class Routing {

  public static $routes;

  public static function get($url, $controller) {
    self::$routes[$url] = $controller;
  }


  public static function post($url, $view) {
    self::$routes[$url] = $view;
  }

  public static function run ($url) {
    $parts= explode("/", $url);
    $action = $parts[0];
    if($action == '') {
        $action = 'dashboard';
    }

    if (!array_key_exists($action, self::$routes)) {
      $error = new ErrorController();
      return $error->fileNotFound();
    }

    $controller = self::$routes[$action];
    $object = new $controller;
    $action = $action ?: 'index';
    if(count($parts) > 1) {
      $params= $parts[1];
      $object->$action($params);
      return;
    }

    $object->$action();

    
  }
}