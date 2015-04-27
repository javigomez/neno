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
			self::loadSettingsFromDb();
		}

		// If the setting doesn't exists, let's return the default value.
		return empty(self::$settings[$settingName]) ? $default : self::$settings[$settingName]['value'];
	}

	/**
	 * Load settings from the database
	 *
	 * @return void
	 */
	private static function loadSettingsFromDb()
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true);
		$query
			->select('*')
			->from('#__neno_settings');

		$db->setQuery($query);
		$settings = $db->loadObjectList();

		self::$settings = array ();

		foreach ($settings as $setting)
		{
			self::$settings[$setting->setting_key] = array ('value' => $setting->setting_value, 'read_only' => $setting->read_only);
		}
	}

	/**
	 * Set the value of a particular property. It will be created if it does not exist before
	 *
	 * @param   mixed   $settingName  Setting name
	 * @param   mixed   $settingValue Setting value
	 * @param   boolean $readOnly     If it should be marked as read only
	 *
	 * @return bool
	 */
	public static function set($settingName, $settingValue, $readOnly = false)
	{
		$refresh = false;

		if (empty(self::$settings[$settingName]))
		{
			self::$settings[$settingName] = array ('value' => $settingValue, 'read_only' => $readOnly);
			$refresh                      = true;
		}
		else
		{
			if (!self::$settings[$settingName]['read_only'])
			{
				self::$settings[$settingName]['value'] = $settingValue;
				$refresh                               = true;
			}
		}

		if ($refresh)
		{
			return self::saveSettingsToDb();
		}

		return false;
	}

	/**
	 * Save the settings into the database
	 *
	 * @return bool
	 */
	private static function saveSettingsToDb()
	{
		$db = JFactory::getDbo();

		/* @var $query NenoDatabaseQueryMysqli */
		$query = $db->getQuery(true);
		$query
			->replace('#__neno_settings')
			->columns(
				array (
					'setting_key',
					'setting_value',
					'read_only'
				)
			);

		foreach (self::$settings as $settingName => $settingData)
		{
			$query->values($db->quote($settingName) . ',' . $db->quote($settingData['value']) . ',' . $db->quote($settingData['read_only']));
		}

		$db->setQuery($query);

		return $db->execute() !== false;
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
			self::loadSettingsFromDb();
		}

		return array_keys(self::$settings);
	}
}
