<?php
/**
 * User: A.Rusakevich
 * Date: 08.08.13
 * Time: 13:35
 */

define("ROOT_PATH", dirname(__FILE__));
define('PROJECT_ROOT', dirname(ROOT_PATH));
define("CORE_PATH", ROOT_PATH . '/core');
define("LIBS_PATH", ROOT_PATH . '/libs');
define("CONF_PATH", ROOT_PATH . '/.conf');
define("MAIN_CONF", CONF_PATH . '/main.conf');
define("COLD_STATIC_DIR", ROOT_PATH . '/store/static');

function fixPath($path) {
    return preg_regplace("/[\/\\]/g", DIRECTORY_SEPARATOR, $path);
}