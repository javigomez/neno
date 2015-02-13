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
 * Class NenoTranslateApiGoogle
 *
 * @since  1.0
 */
class NenoTranslateApiGoogle extends NenoTranslateApi
{
	/**
	 * Translate text using google api
	 *
	 * @param string $text
	 *
	 * @return json
	 */
	public function translate($text)
	{
            $apiKey = 'AIzaSyBoWdaSTbZyrRA9RnKZOZZuKeH2l4cdrn8';    	
            $url = 'https://www.googleapis.com/language/translate/v2?key=' . $apiKey . '&q=' . rawurlencode($text) . '&source=en&target=fr';
            
            // Create an instance of a default JHttp object.
            $http = JHttpFactory::getHttp();
            
            // Invoke the GET request.
            $response = $http->get($http);
            print_r($response);
            if($response != 200)
	        {
                echo 'Fetching translation failed! Server response code:' . $response; 
                //echo 'Error description: ' . $responseDecoded['error']['errors'][0]['message'];                
            }
            else 
	       {
        	echo 'Source: ' . $text . '<br>';
        	//echo 'Translation: ' . $responseDecoded['data']['translations'][0]['translatedText']; 
	       }
    	
	}
	
}
