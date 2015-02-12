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
jimport('neno.translate.api.google');

class NenoControllerDemo extends JControllerLegacy
{
	/**
	 * Method to handle ajax call for google translation
	 *	 
	 * @return json
	 */
	public function ajaxTranslate()
	{ 
		$jinput = JFactory::getApplication()->input;
		$api = $jinput->get('api', '', 'string');
		$text = $jinput->get('source', '', 'string');	
			
		if(!empty($text))
		{
			// select the api as per request
			switch($api)
			{
				case "google":
				$neno_translate = new NenoTranslateApiGoogle();
				break;						
			}			
		}	
		    $result = $neno_translate->translate($text);	
			print_r($result);
			exit;
	}

}
