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
	 * {@inheritdoc}
	 *
	 * @param   Joomla\Registry\Registry $options   JHttp client options
	 * @param   JHttpTransport           $transport JHttp client transport
	 */
	public function __construct(Joomla\Registry\Registry $options = null, JHttpTransport $transport = null)
	{
		parent::__construct();

		// Get the api key
		$this->apiKey = NenoSettings::get('translator_api_key');
	}

	/**
	 * Translate text using yandex api
	 *
	 * @param   string $text   text to translate
	 * @param   string $source source language
	 * @param   string $target target language
	 *
	 * @return string
	 */
	public function translate($text, $source, $target)
	{
		// Convert from JISO to ISO codes
		$target = $this->convertFromJisoToIso($target);

		// Language parameter for url
		$source = $this->convertFromJisoToIso($source);
		$lang   = $source . "-" . $target;

		$apiKey = NenoSettings::get('translator_api_key');

		// For POST requests, the maximum size of the text being passed is 10000 characters.
		if (mb_strlen($text) <= 10000)
		{
			$url = 'https://translate.yandex.net/api/v1.5/tr.json/translate?key=' . $apiKey . '&lang=' . $lang;

			// Invoke the POST request.
			$response = $this->post($url, array ('text' => $text));

			$text = null;

			// Log it if server response is not OK.
			if ($response->code != 200)
			{
				NenoLog::log('Yandex API failed with response: ' . $response->code, 1);
				$responseData = json_decode($response->body, true);
				throw new Exception(JText::_('COM_NENO_EDITOR_YANDEX_ERROR_CODE_' . $responseData['code']), $responseData['code']);
			}
			else
			{
				$responseBody = json_decode($response->body);
				$text         = $responseBody->text[0];
			}

			return $text;
		}
		else
		{
			throw new Exception('ERR_TEXT_TOO_LONG', 413);
		}
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
		$isoTag    = strtolower($jisoParts[0]);

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
