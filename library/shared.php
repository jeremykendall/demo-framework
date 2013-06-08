<?php

use DEMO\Application\Controllers;
use DEMO\Framework;
use DEMO\Tools;
use DEMO\Application\Models;
use Inflection\Inflection;

/** Check if environment is development and display and/or log errors accortdingly. **/
function setReporting()
{
    if (DEVELOPMENT_ENVIRONMENT == true) {
        error_reporting(-1);
        ini_set('display_errors', 'On');
        ini_set('log_errors', 'On');
        ini_set('error_log', ROOT . DS . 'tmp' . DS . 'logs' . DS . 'error.log');
    } else {
        error_reporting(E_ALL);
        ini_set('display_errors', 'Off');
        ini_set('log_errors', 'On');
        ini_set('error_log', ROOT . DS . 'tmp' . DS . 'logs' . DS . 'error.log');
    }
}

/** Secondary Call Function **/
function performAction($controller, $action, $queryString = null, $render = 0)
{
    $controllerName = '\\DEMO\\Application\\Controllers\\' . ucfirst($controller) . 'Controller';
    $dispatch = new $controllerName($controller, $action);
    $dispatch->render = $render;
    return call_user_func_array(array ($dispatch, $action), $queryString);
}

/** Routing **/
function routeURL($url, $routing)
{
    foreach ($routing as $pattern => $result) {
        if (preg_match($pattern, $url)) {
            return preg_replace($pattern, $result, $url);
        }
    }

    return ($url);
}

/** Main Call Function **/
function callHook($url, $default, $routing, Inflection $inflection)
{
    var_dump($url);
    var_dump($default);
    
    $queryString = array ();

    if (!isset($url)) {
        $controller = $default['controller'];
        $action = $default['action'];
    } else {
        $url = routeURL($url, $routing);
        $urlArray = array ();
        $urlArray = explode("/", $url);
        $controller = $urlArray[0];
        array_shift($urlArray);
        if (isset($urlArray[0])) {
            $action = $urlArray[0];
            array_shift($urlArray);
        } else {
            $action = 'index'; // Default Action
        }
        $queryString = $urlArray;
    }

    $controllerName = '\\DEMO\\Application\\Controllers\\' . ucfirst($controller) . 'Controller';

    $dispatch = new $controllerName($controller, $action, $inflection);

    if ((int) method_exists($controllerName, $action)) {
        call_user_func_array(array ($dispatch, "beforeAction"), $queryString);
        call_user_func_array(array ($dispatch, $action), $queryString);
        call_user_func_array(array ($dispatch, "afterAction"), $queryString);
    } else {
        /* Error Generation Code Here */
        //FIXME: Yeah, I have no error handling.
    }
}
