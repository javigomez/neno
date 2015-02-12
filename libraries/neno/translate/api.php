<?php

/**
 * @package     Neno
 * @subpackage  TranslateApi
 *
 * @copyright   Copyright (c) 2014 Jensen Technologies S.L. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('JPATH_NENO') or die;

/**
 * Class NenoTranslateApi
 *
 * @since  1.0
 */
abstract class NenoTranslateApi
{
	/**
	 * Method to translate content
	 *
	 * @param   NenoTranslateApi $text
	 *
	 * @return json
	 */
	abstract public function translate($text);
	
}
