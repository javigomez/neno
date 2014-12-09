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
 * Class NenoTranslateTranslatorAdapter
 *
 * @since  1.0
 */
abstract class NenoTranslateTranslatorAdapter
{
	/**
	 * @var string
	 */
	private $name;

	/**
	 * Constructor
	 *
	 * @param   string  $name  Translator Name
	 */
	public function __construct($name)
	{
		$this->name = $name;
	}

	/**
	 * Method to translate a certain string
	 *
	 * @param   string  $string  String to translate
	 *
	 * @return void
	 */
	public abstract function translate($string);
}
