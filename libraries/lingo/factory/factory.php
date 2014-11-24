<?php

/**
 * @package     Lingo
 * @subpackage  Factory
 *
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('JPATH_LINGO') or die;

/**
 * Description of LingoFactory
 *
 * @author  Jensen Technologies <info@notwebdesign.com>
 *
 * @since   1.0
 */
class LingoFactory extends JFactory
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

		$host     = $conf->get('host');
		$user     = $conf->get('user');
		$password = $conf->get('password');
		$database = $conf->get('db');
		$prefix   = $conf->get('dbprefix');
		$driver   = $conf->get('dbtype');
		$debug    = $conf->get('debug');

		$options = array(
			'driver' => $driver
		, 'host'     => $host
		, 'user'     => $user
		, 'password' => $password
		, 'database' => $database
		, 'prefix'   => $prefix
		);

		try
		{
			LingoDatabaseDriver::clearInstances();
			$db = LingoDatabaseDriver::getInstance($options);
		}
		catch ( RuntimeException $ex )
		{
			jexit('Database Error: ' . $ex->getMessage());
		}

		$db->setDebug($debug);

		return $db;
	}
}
