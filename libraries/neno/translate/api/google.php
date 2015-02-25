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
 * Class NenoTranslateApiGoogle
 *
 * @since  1.0
 */
class NenoTranslateApiGoogle extends NenoTranslateApi
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
		$this->methodName = 'Google Translate';

		// Get the api key
		$this->apiKey = $this->getApiKey($this->methodName);
	}

	/**
	 * Translate text using google api
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
		$source = $this->convertFromJisoToIso($source);
		$target = $this->convertFromJisoToIso($target);

		$isoPair = $source . ',' . $target;

		// Check availability of language pair for translation
		$isAvailable = $this->isTranslationAvailable($isoPair, $this->methodName);

		if (!$isAvailable)
		{
			return null;
		}

		$url = 'https://www.googleapis.com/language/translate/v2?key=' . $this->apiKey . '&q=' . rawurlencode($text) . '&source=' . $source . '&target=' . $target;

		// Invoke the GET request.
		$response = $this->get($url);

		$text = null;

		// Log it if server response is not OK.
		if ($response->code != 200)
		{
			NenoLog::log('Google api failed with response: ' . $response->code, 1);
		}
		else
		{
			$responseBody = json_decode($response->body);
			$text         = $responseBody->data->translations[0]->translatedText;
		}

		return $text;
	}

	/**
	 * Method to make supplied language codes equivalent to google api codes
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
			case 'zh':
				$iso = $jiso;
				break;

			case 'he':
				$iso = 'iw';
				break;

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
