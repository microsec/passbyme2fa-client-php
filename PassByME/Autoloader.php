<?php

namespace PassByME;

/**
 * Autoloader functionality for loading classes.
 * Use this if you do not use composer autoload.
 * 
 * @author     Microsec Ltd. <development@passbyme.com>
 * @copyright  (c) 2015, Microsec Ltd.
 */


/**
 * Load files by namespace convention.
 * 
 * @param namespace $namespace
 * @return mixed
 */
function load($namespace)
{
    $path = str_replace('\\', DIRECTORY_SEPARATOR, $namespace, $count);
    if ($count > 0) {
        $ret = include_once(dirname(__DIR__) . DIRECTORY_SEPARATOR  . $path . '.php');
    } else {
        $ret = false;
    }

    return $ret;
}

spl_autoload_register(__NAMESPACE__ . '\load');
