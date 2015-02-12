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
		$apiKey = 'AIzaSyCeyAoTQ7fDT9dUE0FNZ3H1CgnqPZreU1c IPS:	178.62.100.46';    	
    	//$url = 'https://www.googleapis.com/language/translate/v2?key=' . $apiKey . '&q=' . rawurlencode($text) . '&source=en&target=fr';
		$url = 'https://www.googleapis.com/language/translate/v2/languages?key=' . $apiKey;

    	$handle = curl_init($url);
    	curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    	$response = curl_exec($handle);
    	$responseDecoded = json_decode($response, true);
    	$responseCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);      //Here we fetch the HTTP response code
   	    curl_close($handle);

   		if($responseCode != 200)
		{
        	echo 'Fetching translation failed! Server response code:' . $responseCode . '<br>';
        	echo 'Error description: ' . $responseDecoded['error']['errors'][0]['message'];
        }
        else 
		{
        	echo 'Source: ' . $text . '<br>';
        	echo 'Translation: ' . $responseDecoded['data']['translations'][0]['translatedText']; 
	    }
	}
	
}
