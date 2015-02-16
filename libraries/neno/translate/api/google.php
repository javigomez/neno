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
	 * @var TranslateApi
	 */
	protected $apiKey;
        
	/**
	 * Translate text using google api
	 *
	 * @param   string $apiKey  the key provided by user
         * @param   string $text    text to translate
	 * @param   string $source  source language
         * @param   string $target  target language
	 *
	 * @return string
	 */
	public function translate($text,$source="en",$target="fr")
	{
            // get the key configured by user              
            $this->apiKey = JComponentHelper::getParams('com_neno')->get('googleApiKey');
            
                if($this->apiKey == "")
                {    
                    // Use default key if not provided
                    $this->apiKey = 'AIzaSyBoWdaSTbZyrRA9RnKZOZZuKeH2l4cdrn8';
                }
                
		$url    = 'https://www.googleapis.com/language/translate/v2?key=' . $this->apiKey . '&q=' . rawurlencode($text) . '&source=' . $source .'&target=' .$target;

		// Invoke the GET request.
		}

	}

	public function getLanguagePairs()
	{
		$url      = 'https://www.googleapis.com/language/translate/v2/languages?key=AIzaSyBoWdaSTbZyrRA9RnKZOZZuKeH2l4cdrn8';
		$response = $this->get($url);

		$responseBody = json_decode($response->body, true);
		$languages    = $responseBody['data']['languages'];
		$db           = JFactory::getDbo();
		foreach ($languages as $key => $language)
		{
			$languagePair                        = new stdClass;
			$languagePair->translation_method_id = 1;
			$languagePair->source_language       = $language['language'];
			foreach ($languages as $otherKey => $otherLanguage)
			{
				if ($key != $otherKey)
				{
					$languagePair->destination_language = $otherLanguage['language'];
					$db->insertObject('#__neno_translation_methods_language_pairs', $languagePair);
				}

			}
		}
	}

}
