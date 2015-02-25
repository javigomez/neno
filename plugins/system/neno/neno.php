<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.Neno
 *
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 *
 */
defined('JPATH_BASE') or die;

/**
 * System plugin for Neno
 *
 * @package     Joomla.Plugin
 * @subpackage  System
 *
 * @since       1.0
 */
class PlgSystemNeno extends JPlugin
{
	/**
	 * Method to register a custom database driver
	 *
	 * @return void
	 */
	public function onAfterInitialise()
	{
		$nenoLoader = JPATH_LIBRARIES . '/neno/loader.php';

		if (file_exists($nenoLoader))
		{
			JLoader::register('NenoLoader', $nenoLoader);

			// Register the Class prefix in the autoloader
			NenoLoader::init();

			// Load custom driver.
			JFactory::$database = null;
			JFactory::$database = NenoFactory::getDbo();
		}
	}

	/**
	 * Event triggered before uninstall an extension
	 *
	 * @param   integer $extensionId Extension ID
	 *
	 * @return void
	 */
	public function onExtensionBeforeUninstall($extensionId)
	{
	}

	/**
	 * Event triggered after install an extension
	 *
	 * @param   JInstaller $installer   Installer instance
	 * @param   integer    $extensionId Extension Id
	 *
	 * @return void
	 */
	public function onExtensionAfterInstall($installer, $extensionId)
	{

	}

	/**
	 * Event triggered after update an extension
	 *
	 * @param   JInstaller $installer   Installer instance
	 * @param   integer    $extensionId Extension Id
	 *
	 * @return void
	 */
	public function onExtensionAfterUpdate($installer, $extensionId)
	{

	}
}
