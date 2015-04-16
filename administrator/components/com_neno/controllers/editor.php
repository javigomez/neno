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
 * Manifest Editor controller class
 *
 * @since  1.0
 */
class NenoControllerEditor extends JControllerAdmin
{
	/**
	 * Get a list of strings
	 *
	 * @return  string
	 */
	public function getStrings()
	{
		NenoLog::log('Method getStrings of NenoControllerEditor called', 3);

		$input = JFactory::getApplication()->input;

		$filterJson     = $input->getString('jsonData');
		$filterOffset   = $input->getString('limitStart');
		$filterLimit    = $input->getString('limit');
		$filterArray    = json_decode($filterJson);
		$filterGroups   = array ();
		$filterElements = array ();
		$filterKeys     = array ();

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
		$app = JFactory::getApplication();

		$app->setUserState('com_neno.editor.group', $filterGroups);
		$app->setUserState('com_neno.editor.element', $filterElements);
		$app->setUserState('com_neno.editor.field', $filterField);

		/* @var $stringsModel NenoModelEditor */
		$editorModel  = $this->getModel('Editor', 'NenoModel');
		$translations = $editorModel->getItems();

		//echo JLayoutHelper::render('strings', $translations, JPATH_NENO_LAYOUTS);

		JFactory::getApplication()->close();
	}

	/**
	 * Method to handle ajax call for google translation
	 *
	 * @return string
	 */
	public function translate()
	{
		$app             = JFactory::getApplication();
		$input           = $app->input;
		$text            = $input->getString('text');
		$workingLanguage = NenoHelper::getWorkingLanguage();
		$defaultLanguage = JFactory::getLanguage()->getDefault();
		$translator      = NenoSettings::get('translator');

		try
		{
			/* @var $nenoTranslate NenoTranslateApi */
			$nenoTranslate = NenoTranslateApi::getAdapter($translator);
			$result        = $nenoTranslate->translate($text, $defaultLanguage, $workingLanguage);

			if ($result == null)
			{
				$result = $text;
			}
		}
		catch (UnexpectedValueException $e)
		{
			$result = $text;
		}

		echo $result;

		$app->close();
	}

	/**
	 * Get a translations
	 *
	 * @return void
	 */
	public function getTranslation()
	{
		$input         = $this->input;
		$translationId = $input->getInt('id');

		if (!empty($translationId))
		{
			$translation = NenoContentElementTranslation::getTranslation($translationId);
			echo JLayoutHelper::render('editor', $translation->prepareDataForView(true), JPATH_NENO_LAYOUTS);
		}

		JFactory::getApplication()->close();
	}

	/**
	 * Save translation as draft
	 *
	 * @return void
	 */
	public function saveAsDraft()
	{
		$input           = $this->input;
		$translationId   = $input->getInt('id');
		$translationText = $input->getHtml('text');

		if ($this->saveTranslation($translationId, $translationText, NenoContentElementTranslation::NOT_TRANSLATED_STATE))
		{
			/* @var $translation NenoContentElementTranslation */
			$translation = NenoContentElementTranslation::load($translationId, false);

			echo json_encode($translation->prepareDataForView());
		}
	}

	/**
	 * Save translation into the database
	 *
	 * @param   int    $translationId   Translation ID
	 * @param   string $translationText Translation Text
	 * @param   int    $changeState     Translation status
	 *
	 * @return bool
	 */
	protected function saveTranslation($translationId, $translationText, $changeState = false)
	{
		/* @var $translation NenoContentElementTranslation */
		$translation = NenoContentElementTranslation::load($translationId, false, true);

		$translation
			->setString($translationText)
			->setState($changeState);

		if ($changeState == NenoContentElementTranslation::TRANSLATED_STATE)
		{
			$translation->setTimeCompleted(new DateTime);
		}

		$result = $translation->persist();

		if ($changeState == NenoContentElementTranslation::TRANSLATED_STATE)
		{
			// Move translation to the shadow table
			$workingLanguage = NenoHelper::getWorkingLanguage();
			$translation->moveTranslationToShadowTable($workingLanguage);
		}

		return $result;
	}

	/**
	 * Save translation as completed
	 *
	 * @return void
	 */
	public function saveAsCompleted()
	{
		$input           = $this->input;
		$translationId   = $input->getInt('id');
		$translationText = $input->getHtml('text');

		if ($this->saveTranslation($translationId, $translationText, NenoContentElementTranslation::TRANSLATED_STATE))
		{
			/* @var $translation NenoContentElementTranslation */
			$translation = NenoContentElementTranslation::load($translationId, false);

			echo json_encode($translation->prepareDataForView());
		}
	}
}
