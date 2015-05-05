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
		$filterJson     = $input->getString('jsonGroupsElements');
		$filterArray    = json_decode($filterJson);
		$filterGroups   = array ();
		$filterElements = array ();
		$filterField    = array ();
		$filterMethods  = array ();
		$filterStatus   = array ();
		$outputLayout   = strtolower($input->getString('outputLayout'));
		$app            = JFactory::getApplication();

		NenoLog::log('Processing filtered json data for getStrings', 3);

		foreach ($filterArray as $filterItem)
		{
			if (NenoHelper::startsWith($filterItem, 'group-') !== false)
			{
				$filterGroups[] = str_replace('group-', '', $filterItem);
			}
			elseif (NenoHelper::startsWith($filterItem, 'table-') !== false)
			{
				$filterElements[] = str_replace('table-', '', $filterItem);
			}
			elseif (NenoHelper::startsWith($filterItem, 'field-') !== false)
			{
				$filterField[] = str_replace('field-', '', $filterItem);
			}
		}

		// Set filters into the request.
		$input->set('group', $filterGroups);
		$input->set('table', $filterElements);
		$input->set('field', $filterField);

		$filterJson  = $input->getString('jsonMethod');
		$filterArray = json_decode($filterJson);

		foreach ($filterArray as $filterItem)
		{
			$filterMethods[] = str_replace('method-', '', $filterItem);
		}

		$input->set('type', $filterMethods);

		$filterJson  = $input->getString('jsonStatus');
		$filterArray = json_decode($filterJson);

		foreach ($filterArray as $filterItem)
		{
			$filterStatus[] = (int) str_replace('status-', '', $filterItem);
		}

		$input->set('status', $filterStatus);

		$app->setUserState('limit', $input->getInt('limit', 20));
		$app->setUserState('limitStart', $input->getInt('limitStart', 0));

		/* @var $stringsModel NenoModelStrings */
		$stringsModel = $this->getModel();
		$translations = $stringsModel->getItems();

		$displayData               = new stdClass;
		$displayData->translations = $translations;
		$displayData->state        = $stringsModel->getState();
		$displayData->pagination   = $stringsModel->getPagination();

		echo JLayoutHelper::render($outputLayout, $displayData, JPATH_NENO_LAYOUTS);

		JFactory::getApplication()->close();
	}

	/**
	 * Get model
	 *
	 * @param   string $name   Model Name
	 * @param   string $prefix Model Prefix
	 * @param   array  $config Model configuration
	 *
	 * @return NenoModelStrings
	 */
	public function getModel($name = '', $prefix = '', $config = array ())
	{
		return parent::getModel('Strings', 'NenoModel', $config);
	}

	/**
	 * Load elements using AJAX
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function getElements()
	{
		$input   = JFactory::getApplication()->input;
		$groupId = $input->getInt('group_id');

		if (!empty($groupId))
		{
			/* @var $group NenoContentElementGroup */
			$group  = NenoContentElementGroup::load($groupId);
			$tables = $group->getTables(false);
			$files  = $group->getLanguageFiles();

			$displayData = array ();

			/* @var $model NenoModelStrings */
			$model                 = $this->getModel();
			$displayData['tables'] = NenoHelper::convertNenoObjectListToJObjectList($tables);
			$displayData['files']  = $files;
			$displayData['state']  = $model->getState();
			$tablesHTML            = JLayoutHelper::render('multiselecttables', $displayData, JPATH_NENO_LAYOUTS);
			echo $tablesHTML;
		}

		JFactory::getApplication()->close();
	}
}
