<?php
/**
 * @version     1.0.0
 * @package     com_lingo
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Soren Beck Jensen <soren@notwebdesign.com> - http://www.notwebdesign.com
 */
// No direct access
defined('_JEXEC') or die;

jimport('joomla.log.log');

/**
 * Lingo debug helper
 */
class LingoDebug extends JLog {

    /**
     * A static method that allows logging of errors and messages
     * @param string $string The log line that should be saved
     * @param int $level 1=error, 2=info, 3=debug
     * @param bool $display_message Weather or not the logged message should be displayed to the user
     * @return bool true on success
     */
    public static function log($string, $level = 2, $display_message=false) {

        //Add an extra tab to debug messages
        if ($level > 2) {
            $string = "\t".$string;
        }

        //Get jLog priority
        $priority = self::getJlogPriorityFromDebugLevel($level);

        //Setup the logging method
        self::setLogMethod();

        //Add the log entry
        self::add($string, $priority, 'com_lingo');
        
        //Show message
        if ($display_message === true) {
            JFactory::getApplication()->enqueueMessage($string);
        }
        
        return true;

    }



    public static function setLogMethod() {

        $options['text_entry_format'] = "{DATETIME}\t{PRIORITY}\t\t{MESSAGE}";
        $options['text_file'] = 'lingo_log.php';

        self::addLogger(
            $options
            , self::ALL
            , array('com_lingo')
        );

    }

    /**
     * Convert our simple priority 1,2,3 to appropriate jLog error integer
     * @param $priority 1,2 or 3
     * @return int Jlog priority integer
     */
    private static function getJlogPriorityFromDebugLevel($priority) {

        if ($priority == 1) {
            return self::ERROR;
        } else if ($priority == 2) {
            return self::INFO;
        } else {
            return self::DEBUG;
        }

    }


}
