<?php
/**
 * @package     Neno
 * @subpackage  TranslateApi
 *
 * @copyright   Copyright (c) 2014 Jensen Technologies S.L. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('JPATH_NENO') or die;
jimport('joomla.application.component.helper');

/**
 * Class NenoTranslateApiYandex
 *
 * @since  1.0
 */
class NenoTranslateApiYandex extends NenoTranslateApi
{
	/**
	 * @var string
	 */
	protected $apiKey;

	/**
	 * Translate text using yandex api
	 *
	 * @param   string $text   text to translate
	 * @param   string $source source language
	 * @param   string $target target language default french
	 *
	 * @return string
	 */
	public function translate($text, $source = "en-US", $target = "fr-FR")
	{
		// Get the key configured by user
		/** @noinspection PhpUndefinedMethodInspection */
		$this->apiKey = JComponentHelper::getParams('com_neno')->get('yandexApiKey');

		// Convert from JISO to ISO codes
		$target = $this->convertFromJisoToIso($target);

		// Language parameter for url
		$source = $this->convertFromJisoToIso($source);
		$lang   = $source . "-" . $target;

		// Check availability of language pair for translation
		$isAvailable = $this->isTranslationAvailable($lang);

		if (!$isAvailable)
		{
			return null;
		}


		if ($this->apiKey == "")
		{
			// Use default key if not provided
			$this->apiKey = 'trnsl.1.1.20150213T133918Z.49d67bfc65b3ee2a.b4ccfa0eaee0addb2adcaf91c8a38d55764e50c0';
		}

		// For POST requests, the maximum size of the text being passed is 10000 characters.
		$textString  = str_split($text, 10000);
		$textStrings = '';

		foreach ($textString as $str)
		{
			$textStrings .= '&text=' . rawurlencode($str);
		}

		$url = 'https://translate.yandex.net/api/v1.5/tr.json/translate?key=' . $this->apiKey . '&lang=' . $lang . $textStrings;

		// Invoke the GET request.
		$response = $this->get($url);

		$text = null;

		// Log it if server response is not OK.
		if ($response->code != 200)
		{
			NenoLog::log('Yandex api failed with response: ' . $response->code, 1);
		}
		else
		{
			$responseBody = json_decode($response->body);
			$text         = $responseBody->text[0];
		}

		return $text;

	}

	/**
	 * Method to make supplied language codes equivalent to yandex api codes
	 *
	 * @param   string $jiso Joomla ISO language code
	 *
	 * @return string
	 */
	public function convertFromJisoToIso($jiso)
	{
		// Split the language code parts using hyphen
		$jisoParts = (explode("-", $jiso));
		$iso2Tag   = strtolower($jisoParts[0]);

		switch ($iso2Tag)
		{
			case "nb":
				$iso2 = "no";
				break;

			default:
				$iso2 = $iso2Tag;
				break;
		}

		return $iso2;
	}

	/**
	 * Method to check if language pair is available or not in yandex api
	 *
	 * @param   string $isoPair ISO2 language code pair
	 *
	 * @return boolean
	 */
	public function isTranslationAvailable($isoPair)
	{
		// Split the language pair using hyphen
		$isoParts = (explode("-", $isoPair));

		$available = 1;

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('*')
			->from($db->quoteName('#__neno_translation_methods_language_pairs', 'mlp'))
			->innerJoin($db->quoteName('#__neno_translation_methods', 'm') . ' ON (' . $db->quoteName('mlp.translation_method_id') . ' = ' . $db->quoteName('m.id') . ')')
			->where($db->quoteName('m.translator_name') . '=' . $db->quote('Yandex Translate'))
			->where('mlp.source_language = ' . $db->quote($isoParts[0]))
			->where('mlp.destination_language = ' . $db->quote($isoParts[1]));

		$db->setQuery($query);
		$db->execute();
		$num_rows = $db->getNumRows();

		if ($num_rows == 0)
		{
			$available = 0;
		}

		return $available;
	}
}
