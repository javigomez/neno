<?php

/**
 * @package     Neno
 * @subpackage  Cache
 *
 * @author      Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('JPATH_NENO') or die;

/**
 * Class NenoCache
 *
 * @since  1.0
 */
class NenoCache
{
	/**
	 * @var JCache
	 */
	protected static $cache = null;

	/**
	 * Get data from the Application cache
	 *
	 * @param   string $cacheId Cache Identifier
	 * @param   mixed  $default Default value for this cache data
	 *
	 * @return mixed Data cached
	 */
	public static function getCacheData($cacheId, $default = null)
	{
		self::initCache();
		$data = self::$cache->get($cacheId);

		// If the data is not in the cache, let's return the default value
		if ($data === false)
		{
			$data = $default;
		}
		else
		{
			$data = unserialize($data);
		}

		return $data;
	}

	/**
	 * Init cache
	 *
	 * @return void
	 */
	protected static function initCache()
	{
		// If cache hasn't been initialise, let's do it
		if (self::$cache === null)
		{
			self::$cache = new JCache(array ('caching' => true, 'checkTime' >= false, 'defaultgroup' => 'neno'));
		}
	}

	/**
	 * Set data into the cache
	 *
	 * @param   string $cacheId Cache Identifier
	 * @param   mixed  $data    Data to cache
	 *
	 * @return void
	 */
	public static function setCacheData($cacheId, $data)
	{
		self::initCache();

		// If the data is null, let's delete that cache file.
		if ($data === null)
		{
			self::$cache->remove($cacheId);
		}
		else
		{
			self::$cache->store(serialize($data), $cacheId);
		}
	}

	/**
	 * Get cache Id
	 *
	 * @param   string $functionName Function name
	 * @param   array  $arguments    Function arguments
	 *
	 * @return string
	 */
	public static function getCacheId($functionName, array $arguments)
	{
		return $functionName . implode('+', $arguments);
	}
}
