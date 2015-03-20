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
		NenoLog::log('Method discoverExtension of NenoControllerGroupsElements called',3);

		// Check all the extensions that haven't been discover yet
		NenoHelper::discoverExtensions();

		NenoLog::log('Redirecting to groupselements view',3);

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
				$contentElementFiles = array($destFile);
			}

			// Add to each content file the path of the extraction location.
			NenoHelper::concatenateStringToStringArray($extractPath . '/', $contentElementFiles);

			NenoLog::log('Parsing element files for readContentElementFile', 3);

			// Parse element file(s)
			NenoHelper::parseContentElementFile(JFile::stripExt($fileData['name']), $contentElementFiles);

			NenoLog::log('Cleaning temporal folder for readContentElementFile', 3);

			// Clean temporal folder
			NenoHelper::cleanFolder(JFactory::getConfig()->get('tmp_path'));
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
	 *
	 */
	public function enableDisableContentElementField()
	{
		NenoLog::log('Method enableDisableContentElementField of NenoControllerGroupsElements called', 3);

		$input = JFactory::getApplication()->input;

		$fieldId         = $input->getInt('fieldId');
		$translateStatus = $input->getBool('translateStatus');

		NenoLog::log('Call to getFieldById of NenoContentElementField', 3);
		/* @var $field NenoContentElementField */
		$field  = NenoContentElementField::getFieldById($fieldId);
		$result = 0;

		// If the table exists, let's work with it.
		if ($field !== false)
		{
			NenoLog::log('Table exists', 2);

			$field->setTranslate($translateStatus);
			$field->persist();

			$stringStatus                  = array();
			$stringStatus['translated']    = $field->getWordsTranslated();
			$stringStatus['queued']        = $field->getWordsQueuedToBeTranslated();
			$stringStatus['changed']       = $field->getWordsSourceHasChanged();
			$stringStatus['notTranslated'] = $field->getWordsNotTranslated();

			NenoLog::log('Call to htmlTranslationBar of NenoHelper', 3);

			$result = NenoHelper::htmlTranslationBar($stringStatus, $translateStatus);
		}

		echo $result;
		JFactory::getApplication()->close();
	}

    
    public function getElements() {
        
        $input = JFactory::getApplication()->input;
        $groupId = $input->getInt('group_id');
        
        /* @var $group NenoContentElementGroup */
        $group = NenoContentElementGroup::load($groupId);
        $tables = $group->getTables();
        $tablesHTML = JLayoutHelper::render('rowelementtable', $tables, JPATH_NENO_LAYOUTS);
        
        
        
        //$files = $group->getLanguageFiles();
        
        echo $tablesHTML;
        
        JFactory::getApplication()->close();
        
        
    }
    
    

}
