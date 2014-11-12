<?php

/**
 * @package Lingo
 * @subpackage Database
 * 
 * @copyright (c) 2014, Jensen Technologies S.L. All rights reserved
 */

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

            // Generate Driver Class name
            $class = 'LingoDatabaseDriver' . ucfirst(strtolower($options['driver']));

            // If the class doesn't exists, we cannot work with this driver.
            if (!class_exists($class)) {

                // Let's using parent method
                return parent::getInstance($options);
            }

            // Let's create our driver instance using the options given.s
            try {
                $instance = new $class($options);
            } catch (RuntimeException $ex) {
                throw new RuntimeException(sprintf('Unable to connect to the database. Error: %s',
                        $ex->getMessage()));
            }

            // Save the instance into the instances set.
            self::$instances[$driverSignature] = $instance;
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

}
