<?php

/**
 * @package Lingo
 * @subpackage Database
 * 
 * @copyright (c) 2014, Jensen Technologies S.L. All rights reserved
 */
defined('JPATH_LINGO') or die;

/**
 * Database driver class extends from Joomla Platform Database Driver class
 *
 * @package Redcore
 * @subpackage Database
 * @since 1.0
 */
abstract class LingoDatabaseDriver extends JDatabaseDriver
{

    public static function getInstance($options = array())
    {
        $options['driver'] = (isset($options['driver'])) ?
                preg_replace('/[^A-Z0-9_\.-]/i', '', $options['driver']) : 'mysqli';

        $options['database'] = (isset($options['database'])) ? $options['database'] : null;

        $options['select'] = (isset($options['select'])) ? $options['select'] : true;

        // Get an option hash to identify the instance
        $driverSignature = md5(serialize($options));

        // Check if the driver has been already instanciated
        if (empty(self::$instances[$driverSignature])) {

            // If the class doesn't exists, we cannot work with this driver.
            if (!self::isMySQL($options['driver'])) {

                // Let's using parent method
                return parent::getInstance($options);
            }

            // Let's create our driver instance using the options given.s
            try {
                /* @var $instance LingoDatabaseDriverMysqlx  */
                $instance = LingoDatabaseDriverMysqlx::getInstance($options);
            } catch (RuntimeException $ex) {
                throw new RuntimeException(sprintf('Unable to connect to the database. Error: %s',
                        $ex->getMessage()));
            }

            // Save the instance into the instances set.
            self::$instances[$driverSignature] = $instance;

            // Load the tables configured to be translatable
            $instance->refreshTranslatableTables();
        }

        return self::$instances[$driverSignature];
    }

    /**
     * Method to clear all the instances.
     * 
     * @return void
     */
    public static function clearInstances()
    {
        self::$instances = null;
    }

    /**
     * Check if the driver is MySQL
     * @param string $driver driver name
     * @return boolean True if it's a mysql driver, false otherwise
     */
    public static function isMySQL($driver)
    {
        return strpos(strtolower($driver), 'mysql') !== false;
    }

}
