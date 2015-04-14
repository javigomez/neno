<?php
/**
 * @package     Neno
 * @subpackage  Settings
 *
 * @copyright   Copyright (c) 2014 Jensen Technologies S.L. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('JPATH_NENO') or die;

/**
 * Class to handle Neno settings
 *
 * @since  1.0
 */
class NenoSettings
{
	/**
	 * @var array
	 */
	private static $settings = null;

	/**
	 * Get the value of a particular property
	 *
	 * @param   mixed      $settingName Setting name
	 * @param   mixed|null $default     Default value in case the setting doesn't exist
	 *
	 * @return mixed
	 */
	public static function get($settingName, $default = null)
	{
		// If the settings haven't been loaded yet, let's load them
		if (self::$settings === null)
		{
			self::loadSettings();
		}

		// If the setting doesn't exists, let's return the default value.
		return empty(self::$settings[$settingName]) ? $default : self::$settings[$settingName];
	}

	/**
	 * Load settings from the database
	 *
	 * @return void
	 */
	private static function loadSettings()
	{
		jimport('joomla.application.component.helper');

		/* @var $settings Joomla\Registry\Registry */
		self::$settings = JComponentHelper::getParams('com_neno')->toArray();
	}

	/**
	 * Get all the settings keys
	 *
	 * @return array
	 */
	public static function getSettingsKeys()
	{
		if (self::$settings === null)
		{
			self::loadSettings();
		}

		return array_keys(self::$settings);
	}
}
