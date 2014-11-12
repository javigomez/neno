<?php

/**
 * @package Lingo
 * @subpackage Database
 * 
 * @copyright (c) 2014, Jensen Technologies S.L. All rights reserved
 */

/**
 * Description of mysql
 *
 * @author victor
 */
class LingoDatabaseDriverMysql extends JDatabaseDriverMysql
{

    public function replacePrefix($sql, $prefix = '#__')
    {
        
        var_dump(JFactory::getLanguage()->getLocale());
        return parent::replacePrefix($sql, $prefix);
    }

}
