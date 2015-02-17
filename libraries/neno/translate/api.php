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
abstract class NenoTranslateApi extends JHttp
{
	/**
	 * Method to translate content
	 *
 	 * @param   string $apiKey  the key provided by user
	 * @param   string $text    text to translate
 	 * @param   string $source  source language
 	 * @param   string $target  target language
 	 *
 	 * @return json
 	 */
	abstract public function translate($text,$source,$target);

	/**
	 * Method to make supplied language codes equivalent to translation api codes
	 *
	 * @param   string $jiso Joomla ISO language code
	 *
	 * @return string
	 */
	abstract public function convertFromJisoToIso($jiso);

	/**
	 * Method to check if language pair is available or not in translation api
	 *
	 * @param   string $iso2Pair ISO2 language code pair
	 *
	 * @return boolen
	 */
	abstract public function isTranslationAvailable($isoPair);

	/**
	 * Method to get supported language pairs for translation from translation api
	 *
	 * @return json
	 */
	abstract public function getApiSupportedLanguagePairs();

	/**
	 * Method to get supported language pairs for translation from our server
	 *
	 * @return json
	 */
	public function getSupportedLanguagePairs()
	{
		echo "working...";
	}

}
