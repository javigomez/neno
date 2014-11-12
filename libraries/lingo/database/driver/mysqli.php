<?php

/**
 * @package Lingo
 * @subpackage Database
 * 
 * @copyright (c) 2014, Jensen Technologies S.L. All rights reserved
 */

/**
 * Description of mysqli
 *
 * @author victor
 */
class LingoDatabaseDriverMysqli extends JDatabaseDriverMysqli
{

    public function replacePrefix($sql, $prefix = '#__')
    {
        return parent::replacePrefix($sql, $prefix);
    }

}
