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
	protected $methodName;

	/**
	 * {@inheritdoc}
	 *
	 * @param JRegistry      $options
	 * @param JHttpTransport $transport
	 *
	 */
	public function __construct(JRegistry $options = null, JHttpTransport $transport = null)
	{
		parent::__construct();

		// Method name for the api
		$this->methodName = 'Yandex Translate';

		// Get the api key
		$this->apiKey = $this->getApiKey($this->methodName);
	}

	/**
	 * Translate text using yandex api
	 *
	 * @param   string $text   text to translate
	 * @param   string $source source language default english
	 * @param   string $target target language default french
	 *
	 * @return string
	 */
	public function translate($text, $source = 'en-US', $target = 'fr-FR')
	{
		// Convert from JISO to ISO codes
		$target = $this->convertFromJisoToIso($target);

		// Language parameter for url
		$source = $this->convertFromJisoToIso($source);
		$lang   = $source . '-' . $target;
		$isoPair = $source . ',' . $target;

		// Check availability of language pair for translation
		$isAvailable = $this->isTranslationAvailable($isoPair, $this->methodName);

		if (!$isAvailable)
		{
			return null;
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
		$jisoParts = (explode('-', $jiso));
		$isoTag   = strtolower($jisoParts[0]);

		switch ($isoTag)
		{
			case 'nb':
				$iso = 'no';
				break;

			default:
				$iso = $isoTag;
				break;
		}

		return $iso;
	}

}
