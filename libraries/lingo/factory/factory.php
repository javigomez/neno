<?php

/**
 * @package       Lingo
 * @subpackage    Factory
 *
 * @copyright (c) 2014, Jensen Technologies S.L. All rights reserved
 */
defined('JPATH_LINGO') or die;

/**
 * Description of LingoFactory
 *
 * @author victor
 */
class LingoFactory extends JFactory
{

    public static function getDbo()
    {
        if (!self::$database)
        {
            self::$database = self::createDbo();
        }

        return self::$database;
    }

    /**
     * @inheritdoc
     */
    protected static function createDbo()
    {
        $conf = self::getConfig();

        $host     = $conf->get('host');
        $user     = $conf->get('user');
        $password = $conf->get('password');
        $database = $conf->get('db');
        $prefix   = $conf->get('dbprefix');
        $driver   = $conf->get('dbtype');
        $debug    = $conf->get('debug');

        $options = array(
            'driver' => $driver
        , 'host'     => $host
        , 'user'     => $user
        , 'password' => $password
        , 'database' => $database
        , 'prefix'   => $prefix
        );

        try
        {
            LingoDatabaseDriver::clearInstances();
            $db = LingoDatabaseDriver::getInstance($options);
        }
        catch (RuntimeException $ex)
        {
            jexit('Database Error: ' . $ex->getMessage());
        }

        $db->setDebug($debug);

        return $db;
    }

}
