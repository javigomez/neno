<?php

/**
 * @package Lingo
 * @subpackage Database
 * 
 * @copyright (c) 2014, Jensen Technologies S.L. All rights reserved
 */
// If the database type is mysqli, let's created a middle class that inherit from the Mysqli drive
if (JFactory::getConfig()->get('dbtype') === 'mysqli') {

    class CommonDriver extends JDatabaseDriverMysqli
    {
        
    }

} else {

    // @TODO JDatabaseDriverMysql is already deprecated, so we should remove this class when the minimum PHP version don't support this extension
    class CommonDriver extends JDatabaseDriverMysql
    {
        
    }

}

/**
 * Database driver class extends from Joomla Platform Database Driver class
 *
 * @package Lingo
 * @subpackage Database
 * @since 1.0
 */
class LingoDatabaseDriverMysqlx extends CommonDriver
{

    public function replacePrefix($sql, $prefix = '#__')
    {
        $sql = LingoDatabaseParser::getCurrentShadowTableName($sql);
        return parent::replacePrefix($sql, $prefix);
    }

}
