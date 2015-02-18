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

	/**
	 * Method to check if language pair is available or not in translation api
	 *
	 * @param   string $isoPair ISO2 language code pair
	 *
	 * @return boolean
	 */
	abstract public function isTranslationAvailable($isoPair);

	/**
	 * Method to get supported language pairs for translation from our server
	 *
	 * @return string JSON string
	 */
	public function getSupportedLanguagePairs()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select($db->quoteName(array ('m.translator_name', 'm.translation_type', 'mlp.source_language', 'mlp.destination_language')))
			->from($db->quoteName('#__neno_translation_methods_language_pairs', 'mlp'))
			->innerJoin($db->quoteName('#__neno_translation_methods', 'm') . ' ON (' . $db->quoteName('mlp.translation_method_id') . ' = ' . $db->quoteName('m.id') . ')');

		$db->setQuery($query);
		$pairs = $db->loadObjectList();

		return json_encode($pairs);
	}
}
