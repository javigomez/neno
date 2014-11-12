<?php

// Define Lingo path constant
if (!defined('JPATH_LINGO')) {
    define('JPATH_LINGO', dirname(__FILE__));
}

class LingoLoader
{

    public static function init()
    {
        JLoader::registerPrefix('Lingo', JPATH_LINGO);
        JLoader::setup();
    }

}
