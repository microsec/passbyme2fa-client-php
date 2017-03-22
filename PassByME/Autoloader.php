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
 * @param string $namespace
 * @throws \Exception
 */
function load($namespace)
{
    $path = str_replace('\\', DIRECTORY_SEPARATOR, $namespace);
    $classFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . $path . '.php';

    if (!is_file($classFile)) {
        throw new \Exception('Autoloader error! No such file: ' . $classFile);
    }

    /** @noinspection PhpIncludeInspection */
    include_once($classFile);
}

spl_autoload_register(__NAMESPACE__ . '\load');
