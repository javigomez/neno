<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.Lingo
 *
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 *
 */
defined('JPATH_BASE') or die;

/**
 * System plugin for Lingo
 *
 * @package     Joomla.Plugin
 * @subpackage  System
 *
 * @since       1.0
 */
class PlgSystemLingo extends JPlugin
{

	/**
	 * Method to register a custom database driver
	 *
	 * @return void
	 */
	public function onAfterInitialise()
	{
		$lingoLoader = JPATH_LIBRARIES . '/lingo/loader.php';

		if (file_exists($lingoLoader))
		{
			JLoader::register('LingoLoader', $lingoLoader);

			// Register the Class prefix in the autoloader
			LingoLoader::init();

			// Load custom driver.
			JFactory::$database = null;
			JFactory::$database = LingoFactory::getDbo();
		}
	}
}
