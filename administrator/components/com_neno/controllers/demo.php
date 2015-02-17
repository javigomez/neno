<?php

/**
 * @package    Neno
 *
 * @author     Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright  Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');
jimport('neno.translate');
//jimport('neno.translate.api.yandex');

class NenoControllerDemo extends JControllerLegacy
{
	/**
	 * Method to handle ajax call for google translation
	 *	 
	 * @return string
	 */
	public function ajaxTranslate()
	{
		$app = JFactory::getApplication();
		$jinput = $app->input;
		$api = $jinput->get('api', '', 'string');
		$text = $jinput->get('source', '', 'string');	
			
		if(!empty($text))
		{
			// select the api as per request
			switch($api)
			{
				case "google":
				$nenoTranslate = new NenoTranslateApiGoogle();
				break;

				case "yandex":
				$nenoTranslate = new NenoTranslateApiYandex();
				break;
			}			
		}
                                                           
		    $result = $nenoTranslate->translate($text);
		    if($result == null)
			{
				$result = "warning";
			}
				print_r($result);

		    exit;
	}

	/**
	 * Method to get supported languages by translation api
	 *
	 * @return json
	 */
	public function getSupportedLangs()
	{
		$app = JFactory::getApplication();
		$jinput = $app->input;
		$api = $jinput->get('api', '', 'string');

		if($api=="yandex")
		{
			$nenoTranslate = new NenoTranslateApiYandex();
		}
		else
		{
			$nenoTranslate = new NenoTranslateApiGoogle();
		}

		$result = $nenoTranslate->getApiSupportedLanguagePairs();
		print_r($result);

		exit;
	}


}
