<?php
/**
 * @package     Neno
 * @subpackage  Controllers
 *
 * @author      Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

/**
 * Manifest Strings controller class
 *
 * @since  1.0
 */
class NenoControllerStrings extends JControllerAdmin
{
	/**
	 * Get a list of strings
	 *
	 * @return  string
	 */
	public function getStrings()
	{
		NenoLog::log('Method getStrings of NenoControllerEditor called', 3);
		$input          = JFactory::getApplication()->input;
		$filterJson     = $input->getString('jsonData');
		$filterArray    = json_decode($filterJson);
		$filterGroups   = array ();
		$filterElements = array ();
		$filterField    = array ();

		NenoLog::log('Processing filtered json data for getStrings', 3);

		foreach ($filterArray as $filterItem)
		{
			if (strpos($filterItem, 'group') !== false)
			{
				$filterGroups[] = str_replace('group', '', $filterItem);
			}
			elseif (strpos($filterItem, 'table') !== false)
			{
				$filterElements[] = str_replace('table', '', $filterItem);
			}
			elseif (strpos($filterItem, 'field') !== false)
			{
				$filterField[] = str_replace('field', '', $filterItem);
			}
		}

		// Set filters into the request.
		$app = JFactory::getApplication();

		$app->setUserState('com_neno.strings.group', $filterGroups);
		$app->setUserState('com_neno.strings.element', $filterElements);
		$app->setUserState('com_neno.strings.field', $filterField);

		/* @var $stringsModel NenoModelStrings */
		$stringsModel = $this->getModel('Strings', 'NenoModel');
		$translations = $stringsModel->getItems();

		echo JLayoutHelper::render('strings', $translations, JPATH_NENO_LAYOUTS);

		JFactory::getApplication()->close();
	}
}
