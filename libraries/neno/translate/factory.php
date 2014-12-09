<?php
/**
 * @package     Neno
 * @subpackage  Translate
 *
 * @copyright   Copyright (c) 2014 Jensen Technologies S.L. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('JPATH_NENO') or die;

/**
 * Class NenoTranslatorFactory
 *
 * @since  1.0
 */
class NenoTranslateFactory
{
	/**
	 * @var array
	 */
	private static $translators = array();

	/**
	 * Get a translator
	 *
	 * @param   string  $name  Translator Name
	 *
	 * @return null|
	 */
	public static function getTranslator($name)
	{
		if (!empty($name))
		{
			$className = 'NenoTranslateTranslator' . ucfirst(strtolower($name));

			$translatorSignature = md5($className);

			if (!isset(self::$translators[$translatorSignature]))
			{
				if (class_exists($className))
				{
					$translator = new $className;

					self::$translators[$translatorSignature] = $translator;
				}

				return null;
			}

			return self::$translators[$translatorSignature];
		}
	}
}
