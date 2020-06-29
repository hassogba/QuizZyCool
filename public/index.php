<?php

/**
 * Front controller
 *
 * PHP version 7.0
 */

/**
 * Composer
 */
require dirname(__DIR__) . '/vendor/autoload.php';


/**
 * Error and Exception handling
 */
error_reporting(E_ALL);
set_error_handler('Core\Error::errorHandler');
set_exception_handler('Core\Error::exceptionHandler');


/**
 * Routing
 */
$router = new Core\Router();

// Add the routes
$router->add('', ['controller' => 'User', 'action' => 'connexion']);

// Inscription connexion déconnexion
$router->add('inscription', ['controller' => 'User', 'action' => 'inscription']);
$router->add('connexion', ['controller' => 'User', 'action' => 'connexion']);
$router->add('deconnexion', ['controller' => 'User', 'action' => 'deconnexion']);

// Enregistrement test
$router->add('nouveau', ['controller' => 'Test', 'action' => 'nouveau']);

// Tests
$router->add('tests', ['controller' => 'Test', 'action' => 'liste']);
$router->add('passer-test-{id:\d+}', ['controller' => 'Test', 'action' => 'passerTest']);
$router->add('exporter-test-{id:\d+}', ['controller' => 'Test', 'action' => 'export']);


$router->add('{controller}/{action}');
    
$router->dispatch($_SERVER['QUERY_STRING']);
