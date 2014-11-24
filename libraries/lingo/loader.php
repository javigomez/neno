<?php

/**
 * @package    Lingo
 *
 * @copyright  Copyright (c) 2014 Jensen Technologies S.L. All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('JPATH_BASE') or die;

// Define Lingo path constant
if (!defined('JPATH_LINGO'))
{
	define('JPATH_LINGO', dirname(__FILE__));
}

/**
 * Class to handle dependencies
 *
 * @package  Lingo
 *
 * @since    1.0
 */
class LingoLoader
{
	/**
	 * Adding Lingo and external libraries to the Class Loader
	 *
	 * @return void
	 *
	 * @since 1.0
	 */
	public static function init()
	{
		// Registering Lingo libraries prefix
		JLoader::registerPrefix('Lingo', JPATH_LINGO);

		// Registering SQL parser Namespace
		JLoader::registerNamespace('PHPSQL', JPATH_LINGO . '/database/sqlparser');
	}
}
