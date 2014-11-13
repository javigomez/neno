<?php

/**
 * @package Lingo
 * @subpackage Database
 * 
 * @copyright (c) 2014, Jensen Technologies S.L. All rights reserved
 */
defined('JPATH_LINGO') or die;

/**
 * Description of mysql
 *
 * @author victor
 */
class LingoDatabaseDriverMysql extends JDatabaseDriverMysql
{

    public function replacePrefix($sql, $prefix = '#__')
    {        
        return parent::replacePrefix($sql, $prefix);
    }

}
