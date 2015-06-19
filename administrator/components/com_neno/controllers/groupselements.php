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

/**
 * Manifest Groups & Elements controller class
 *
 * @since  1.0
 */
class NenoControllerGroupsElements extends JControllerAdmin
{
	/**
	 * Method to import tables that need to be translated
	 *
	 * @return void
	 */
	public function discoverExtensions()
	{
		NenoLog::log('Method discoverExtension of NenoControllerGroupsElements called', 3);

		// Check all the extensions that haven't been discover yet
		NenoHelperBackend::groupingTablesNotDiscovered();

		NenoLog::log('Redirecting to groupselements view', 3);

		$this
			->setRedirect('index.php?option=com_neno&view=groupselements')
			->redirect();
	}

	/**
	 * Read content files
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function readContentElementFile()
	{
		NenoLog::log('Method readContentElementFile of NenoControllerGroupsElements called', 3);

		jimport('joomla.filesystem.file');

		NenoLog::log('Trying to move content element files', 3);

		$input       = JFactory::getApplication()->input;
		$fileData    = $input->files->get('content_element');
		$destFile    = JFactory::getConfig()->get('tmp_path') . '/' . $fileData['name'];
		$extractPath = JFactory::getConfig()->get('tmp_path') . '/' . JFile::stripExt($fileData['name']);

		// If the file has been moved successfully, let's work with it.
		if (JFile::move($fileData['tmp_name'], $destFile) === true)
		{
			NenoLog::log('Content element files moved successfully', 2);

			// If the file is a zip file, let's extract it
			if ($fileData['type'] == 'application/zip')
			{
				NenoLog::log('Extracting zip content element files', 3);

				$adapter = JArchive::getAdapter('zip');
				$adapter->extract($destFile, $extractPath);
				$contentElementFiles = JFolder::files($extractPath);
			}
			else
			{
				$contentElementFiles = array ($destFile);
			}

			// Add to each content file the path of the extraction location.
			NenoHelper::concatenateStringToStringArray($extractPath . '/', $contentElementFiles);

			NenoLog::log('Parsing element files for readContentElementFile', 3);

			// Parse element file(s)
			NenoHelperBackend::parseContentElementFile(JFile::stripExt($fileData['name']), $contentElementFiles);

			NenoLog::log('Cleaning temporal folder for readContentElementFile', 3);

			// Clean temporal folder
			NenoHelperBackend::cleanFolder(JFactory::getConfig()->get('tmp_path'));
		}

		NenoLog::log('Redirecting to groupselements view', 3);

		$this
			->setRedirect('index.php?option=com_neno&view=groupselements')
			->redirect();
	}

	/**
	 * Enable/Disable a database table to be translate
	 *
	 * @return void
	 */
	public function enableDisableContentElementTable()
	{
		NenoLog::log('Method enableDisableContentElementTable of NenoControllerGroupsElements called', 3);

		$input = JFactory::getApplication()->input;

		$tableId         = $input->getInt('tableId');
		$translateStatus = $input->getBool('translateStatus');

		NenoLog::log('Call to getTableById of NenoContentElementTable', 3);

		$table  = NenoContentElementTable::getTableById($tableId);
		$result = 0;

		// If the table exists, let's work with it.
		if ($table !== false)
		{
			NenoLog::log('Table exists', 2);

			$table->markAsTranslatable($translateStatus);
			$table->persist();

			$result = 1;
		}

		echo $result;
		JFactory::getApplication()->close();
	}

	/**
	 * Toggle field translate field
	 *
	 * @return void
	 */
	public function toggleContentElementField()
	{
		NenoLog::log('Method toggleContentElementField of NenoControllerGroupsElements called', 3);

		$input = JFactory::getApplication()->input;

		$fieldId         = $input->getInt('fieldId');
		$translateStatus = $input->getBool('translateStatus');

		/* @var $field NenoContentElementField */
		$field = NenoContentElementField::load($fieldId, false, true);

		// If the table exists, let's work with it.
		if ($field !== false)
		{
			$field->setTranslate($translateStatus);

			if ($field->persist() === false)
			{
				NenoLog::log('Error saving new state!', NenoLog::PRIORITY_ERROR);
			}
		}

		JFactory::getApplication()->close();
	}

	/**
	 * Toggle translate status for tables
	 *
	 * @return void
	 */
	public function toggleContentElementTable()
	{
		NenoLog::log('Method toggleContentElementTable of NenoControllerGroupsElements called', 3);

		$input = JFactory::getApplication()->input;

		$tableId         = $input->getInt('tableId');
		$translateStatus = $input->getBool('translateStatus');

		/* @var $table NenoContentElementTable */
		$table = NenoContentElementTable::getTableById($tableId);

		// If the table exists, let's work with it.
		if ($table !== false)
		{
			$table->setTranslate($translateStatus);

			if ($table->persist() === false)
			{
				NenoLog::log('Error saving new state!', NenoLog::PRIORITY_ERROR);
			}
		}

		JFactory::getApplication()->close();
	}

	/**
	 * Get elements
	 *
	 * @return void
	 */
	public function getElements()
	{
		$input   = JFactory::getApplication()->input;
		$groupId = $input->getInt('group_id');

		/* @var $group NenoContentElementGroup */
		$group                 = NenoContentElementGroup::load($groupId);
		$tables                = $group->getTables();
		$files                 = $group->getLanguageFiles();
		$displayData           = array ();
		$displayData['group']  = $group->prepareDataForView();
		$displayData['tables'] = NenoHelper::convertNenoObjectListToJObjectList($tables);
		$displayData['files']  = NenoHelper::convertNenoObjectListToJObjectList($files);
		$tablesHTML            = JLayoutHelper::render('rowelementtable', $displayData, JPATH_NENO_LAYOUTS);

		echo $tablesHTML;

		JFactory::getApplication()->close();
	}


	public function getTranslationMethodSelector()
	{
		$app             = JFactory::getApplication();
		$input           = $this->input;
		$n               = $input->getInt('n', 0);
		$groupId         = $input->getInt('group_id');
		$selectedMethods = $input->get('selected_methods', array (), 'ARRAY');

		$translationMethods = NenoHelper::loadTranslationMethods();

		if (!empty($groupId))
		{
			$group = NenoContentElementGroup::load($groupId)->prepareDataForView();
		}
		else
		{
			$group                               = new stdClass;
			$group->assigned_translation_methods = array ();
		}

		// Ensure that we know what was selected for the previous selector
		if (($n > 0 && !isset($selectedMethods[$n - 1])) || ($n > 0 && $selectedMethods[$n - 1] == 0))
		{
			JFactory::getApplication()->close();
		}

		// As a safety measure prevent more than 5 selectors and always allow only one more selector than already selected
		if ($n > 4 || $n > count($selectedMethods) + 1)
		{
			$app->close();
		}

		// Reduce the translation methods offered depending on the parents
		if ($n > 0 && !empty($selectedMethods))
		{
			$parentMethod                = $selectedMethods[$n - 1];
			$acceptableFollowUpMethodIds = $translationMethods[$parentMethod]->acceptable_follow_up_method_ids;
			$acceptableFollowUpMethods   = explode(',', $acceptableFollowUpMethodIds);

			foreach ($translationMethods as $k => $translationMethod)
			{
				if (!in_array($k, $acceptableFollowUpMethods))
				{
					unset($translationMethods[$k]);
				}
			}
		}

		// If there are no translation methods left then return nothing
		if (!count($translationMethods))
		{
			$app->close();
		}

		// Prepare display data
		$displayData                                 = array ();
		$displayData['translation_methods']          = $translationMethods;
		$displayData['assigned_translation_methods'] = $group->assigned_translation_methods;
		$displayData['n']                            = $n;

		$selectorHTML = JLayoutHelper::render('translationmethodselector', $displayData, JPATH_NENO_LAYOUTS);

		echo $selectorHTML;

		$app->close();
	}

	/**
	 * Changing filter
	 *
	 * @return void
	 */
	public function changeFieldFilter()
	{
		$input = $this->input;
		$app   = JFactory::getApplication();

		$fieldId = $input->getInt('fieldId');
		$filter  = $input->getWord('filter');

		if (!empty($fieldId))
		{
			/* @var $field NenoContentElementField */
			$field = NenoContentElementField::load($fieldId, false, true);

			if (!empty($field))
			{
				$field
					->setFilter($filter)
					->persist();
			}
		}

		$app->close();
	}
}
