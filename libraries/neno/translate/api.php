<?php

/**
 * @package     Neno
 * @subpackage  TranslateApi
 *
 * @copyright   Copyright (c) 2014 Jensen Technologies S.L. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Class NenoTranslateApi
 *
 * @since  1.0
 */
abstract class NenoTranslateApi extends JHttp
{
	/**
	 * @var array
	 */
	private static $adapters = array ();

	/**
	 * Get translator API
	 *
	 * @param   string $apiName API Name
	 *
	 * @return NenoTranslateApi
	 */
	public static function getAdapter($apiName)
	{
		if (!isset(self::$adapters[$apiName]))
		{
			// Try to load the adapter object
			$class = 'NenoTranslateApi' . ucfirst($apiName);

			if (!class_exists($class) || empty($apiName))
			{
				throw new UnexpectedValueException('Unable to load api', 500);
			}

			self::$adapters[$apiName] = new $class;
		}

		return self::$adapters[$apiName];
	}

	/**
	 * Method to translate content
	 *
	 * @param   string $text   text to translate
	 * @param   string $source source language
	 * @param   string $target target language
	 *
	 * @return string
	 */
	abstract public function translate($text, $source, $target);

	/**
	 * Method to make supplied language codes equivalent to translation api codes
	 *
	 * @param   string $jiso Joomla ISO language code
	 *
	 * @return string
	 */
	abstract public function convertFromJisoToIso($jiso);
}
