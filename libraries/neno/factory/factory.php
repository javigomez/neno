<?php

/**
 * @package     Neno
 * @subpackage  Factory
 *
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('JPATH_NENO') or die;

/**
 * Description of NenoFactory
 *
 * @author  Jensen Technologies <info@notwebdesign.com>
 *
 * @since   1.0
 */
class NenoFactory extends JFactory
{
	/**
	 * Get Database driver object
	 *
	 * @return JDatabaseDriver
	 *
	 * @since 1.0
	 */
	public static function getDbo()
	{
		if (!self::$database)
		{
			self::$database = self::createDbo();
		}

		return self::$database;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return JDatabaseDriver
	 *
	 * @since 1.0
	 */
	protected static function createDbo()
	{
		$conf = self::getConfig();

		/** @noinspection PhpUndefinedMethodInspection */
		$host     = $conf->get('host');
		/** @noinspection PhpUndefinedMethodInspection */
		$user     = $conf->get('user');
		/** @noinspection PhpUndefinedMethodInspection */
		$password = $conf->get('password');
		/** @noinspection PhpUndefinedMethodInspection */
		$database = $conf->get('db');
		/** @noinspection PhpUndefinedMethodInspection */
		$prefix   = $conf->get('dbprefix');
		/** @noinspection PhpUndefinedMethodInspection */
		$driver   = $conf->get('dbtype');
		/** @noinspection PhpUndefinedMethodInspection */
		$debug    = $conf->get('debug');

		$options = array (
			'driver' => $driver
		, 'host'     => $host
		, 'user'     => $user
		, 'password' => $password
		, 'database' => $database
		, 'prefix'   => $prefix
		);

		$db = null;

		try
		{
			NenoDatabaseDriver::clearInstances();
			$db = NenoDatabaseDriver::getInstance($options);
			$db->setDebug($debug);
		}
		catch (RuntimeException $ex)
		{
			jexit('Database Error: ' . $ex->getMessage());
		}

		return $db;
	}
}
