<?php

namespace App\Library\Lazada;

class Autoloader
{
    /**
       * Autoloade Class in SDK.
       * PS: only load SDK class
       * @param string $class class name
       * @return void
       */
    public static function autoload($class)
    {
        $name = $class;
        if (false !== strpos($name, '\\')) {
            $name = strstr($class, '\\', true);
        }

        $filename = LAZOP_AUTOLOADER_PATH."/lazop/".$name.".php";
        if (is_file($filename)) {
            include $filename;
            return;
        }
    }
}

// Fixed: Use fully qualified class name for autoloader registration
spl_autoload_register('App\Library\Lazada\Autoloader::autoload');
