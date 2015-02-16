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
		$response = $this->get($url);	
                
                // Log it if server response is not OK.
              	if ($response->code != 200)
		{                   
                    NenoLog::log('Google api failed with response: ' . $response->code, 1);
		}
		else
		{
                    $reponseBody=json_decode($response->body);
                    return $reponseBody->data->translations[0]->translatedText;
		}

	}

}
