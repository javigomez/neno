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
	protected $apiKey;
        
	/**
	 * Translate text using google api
	 *
	 * @param   string $text    text to translate
	 * @param   string $source  source language default english
	 * @param   string $target  target language default french fr-FR
	 *
	 * @return string
	 */
	public function translate($text,$source="en-US",$target="fr-FR")
	{
        // get the key configured by user
		$this->apiKey = JComponentHelper::getParams('com_neno')->get('googleApiKey');

		// convert from JISO to ISO codes
		$source = $this->convertFromJisoToIso($source);
		$target = $this->convertFromJisoToIso($target);

		$isoPair = $source."-".$target;

		// check availability of langauage pair for translation
		$isAvailable = $this->isTranslationAvailable($isoPair);

			if(!$isAvailable)
			{
			  return null;
			}

			if($this->apiKey == "")
			{
            	 // Use default key if not provided
				 $this->apiKey = 'AIzaSyBoWdaSTbZyrRA9RnKZOZZuKeH2l4cdrn8';
			}
                
		$url    = 'https://www.googleapis.com/language/translate/v2?key=' . $this->apiKey . '&q=' . rawurlencode($text) . '&source=' . $source .'&target=' .$target;

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
				$responseBody=json_decode($response->body);
				$text = $responseBody->data->translations[0]->translatedText;
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
		// split the language code parts using hypen
		$jisoParts = (explode("-",$jiso));
		$iso2Tag = strtolower($jisoParts[0]);

		switch($iso2Tag)
		{
			case "zh":
				$iso2 = $jiso;
				break;

			case "he":
				$iso2 = "iw";
				break;

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
	 * Method to check if language pair is available or not in google api
	 *
	 * @param   string $iso2Pair ISO2 language code pair
	 *
	 * @return boolen
	 */
	public function isTranslationAvailable($isoPair)
	{
		// split the language pair using hypen
		$isoParts = (explode("-",$isoPair));

		// array of iso2 language codes not available in google api
		$languages = array("sy", "ug", "si", "bs");
		$available = 1;

		foreach($isoParts as $part)
		{
			if (in_array($part , $languages))
			{
				$available = 0;
			}
		}

		return $available;
	}

	/**
	 * Method to get supported language pairs for translation from translation api
	 *
	 * @return json
	 */
	 public function getApiSupportedLanguagePairs()
	 {
		 // get the key configured by user
		 $this->apiKey = JComponentHelper::getParams('com_neno')->get('googleApiKey');

		 if($this->apiKey == "")
		 {
			 // Use default key if not provided
			 $this->apiKey = 'AIzaSyBoWdaSTbZyrRA9RnKZOZZuKeH2l4cdrn8';
		 }

		 $url    = 'https://www.googleapis.com/language/translate/v2/languages?key='.$this->apiKey;

		 // Invoke the GET request.
		 $response = $this->get($url);

		 $json = null;

		 // Log it if server response is not OK.
		 if ($response->code != 200)
		 {
			 NenoLog::log('Google api failed with response: ' . $response->code, 1);
		 }
		 else
		 {
			 $json = $response->body;
		 }

		 return $json;
	 }
}
