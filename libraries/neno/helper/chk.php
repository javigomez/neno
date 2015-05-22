<?php

/**
 * @package     Neno
 * @subpackage  Helper
 *
 * @author      Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('JPATH_NENO') or die;

/**
 * Neno chk helper.
 *
 * @since  1.0
 */
class NenoHelperChk extends NenoHelperLicense
{
    public static function chk() {
        $licenseData = self::getLicenseData();
        if (count($licenseData) !== 4)
        {
            return false;
        }
        if (self::checkDomainMatch($licenseData[2]) === false)
        {
			return false;
		}
        if (strtotime($licenseData[3]) < time())
        {
            return false;
        }
        return true;
    }
    
    public static function checkDomainMatch($domain)
    {
        if (
                strpos(JUri::root(), $domain) === false 
            )
        {
            return false;
        } 
        else
        {
            return true;
        }
        
    }
    
}


