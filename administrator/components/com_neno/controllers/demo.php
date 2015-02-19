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

class NenoControllerDemo extends JControllerLegacy
{
	/**
	 * Method to handle ajax call for google translation
	 *
	 * @return string
	 */
	public function ajaxTranslate()
	{
		$app   = JFactory::getApplication();
		$input = $app->input;
		$api   = $input->get('api', '', 'string');
		$text  = $input->get('source', '', 'string');
		$nenoTranslate = null;

		if (!empty($text))
		{
			// Select the api as per request
			switch ($api)
			{
				case "google":
					$nenoTranslate = new NenoTranslateApiGoogle;
					break;

				case "yandex":
					$nenoTranslate = new NenoTranslateApiYandex;
					break;
			}
		}

		$result = $nenoTranslate->translate($text);
		if ($result == null)
		{
			$result = "warning";
		}
		print_r($result);

		exit;
	}

	/**
	 * Method to get supported languages by translation api
	 *
	 * @return string
	 */
	public function getSupportedLangs()
	{
		$app   = JFactory::getApplication();
		$input = $app->input;
		$api   = $input->get('api', '', 'string');
		$result = NenoTranslateApi::getSupportedLanguagePairs($api);
		print_r($result);
		exit;
	}
}
