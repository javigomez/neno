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
 * Manifest Strings controller class
 *
 * @since  1.0
 */
class NenoControllerInstallation extends JControllerAdmin
{
	/**
	 * Load installation step
	 *
	 * @return void
	 */
	public function loadInstallationStep()
	{
		$step = NenoSettings::get('installation_status', 0);

		if (empty($step))
		{
			$layout = JLayoutHelper::render('installationgetstarted', null, JPATH_NENO_LAYOUTS);
		}
		else
		{
			$layout = JLayoutHelper::render('installationstep' . $step, $this->getDataForStep($step), JPATH_NENO_LAYOUTS);
		}

		echo $layout;

		JFactory::getApplication()->close();
	}

	/**
	 * Get data for the installation step
	 *
	 * @param   int $step Step number
	 *
	 * @return stdClass
	 */
	protected function getDataForStep($step)
	{
		$data = new stdClass;

		switch ($step)
		{
			case 1:
				$language            = JFactory::getLanguage();
				$languages           = NenoHelper::findLanguages(true);
				$data->select_widget = JHtml::_('select.genericlist', $languages, 'source_language', null, 'iso', 'name', $language->getDefault());
				break;
		}

		return $data;
	}

	/**
	 * Process installation step
	 *
	 * @return void
	 */
	public function processInstallationStep()
	{
		$step        = NenoSettings::get('installation_status', 0);
		$moveForward = true;
		$app         = JFactory::getApplication();
		$response    = array ('status' => 'ok');

		if ($step != 0)
		{
			$methodName = 'validateStep' . (int) $step;

			// Validate data.
			if (method_exists($this, $methodName))
			{
				$moveForward = $this->{$methodName}();
			}
			else
			{
				$app->enqueueMessage(JText::sprintf('COM_NENO_INSTALLATION_ERROR_VALIDATION_METHOD_DOES_NOT_EXIST', $methodName), 'error');
				$moveForward = false;
			}
		}

		if ($moveForward)
		{
			NenoSettings::set('installation_status', $step + 1);
		}
		else
		{
			$response['status'] = 'err';
			$messagesQueued     = $app->getMessageQueue();
			$messages           = array ();

			foreach ($messagesQueued as $messageQueued)
			{
				if ($messageQueued['type'] === 'error')
				{
					$messages[] = $messageQueued['message'];
				}
			}

			$response['error_messages'] = $messages;
		}

		echo json_encode($response);

		JFactory::getApplication()->close();
	}

	public function doMenus()
	{
		NenoHelper::createMenuStructure();
	}

	public function checks()
	{
		$app             = JFactory::getApplication();
		$languages       = JFactory::getLanguage()->getKnownLanguages();
		$defaultLanguage = JFactory::getLanguage()->getDefault();

		foreach ($languages as $language)
		{
			if ($language['tag'] != $defaultLanguage)
			{
				if (NenoHelper::isLanguageFileOutOfDate($language['tag']))
				{
					$app->enqueueMessage('Language file of ' . $language['name'] . ' out of date. Please check', 'error');
				}

				if (!NenoHelper::hasContentCreated($language['tag']))
				{
					$app->enqueueMessage('We have detect that ' . $language['name'] . ' language does not have created a content record', 'error');
				}

				$contentCounter = NenoHelper::contentCountInOtherLanguages($language['tag']);

				if ($contentCounter !== 0)
				{
					$app->enqueueMessage('We have detect content in ' . $language['name'] . ' that have not been moved to the shadow tables', 'error');
				}
			}
		}
	}

	/**
	 * Get translation method selector
	 *
	 * @return void
	 */
	public function getTranslationMethodSelector()
	{
		$input               = $this->input;
		$n                   = $input->getInt('n', 0);
		$selected_methods    = $input->get('selected_methods', array (), 'ARRAY');
		$translation_methods = NenoHelper::loadTranslationMethods();
		$app                 = JFactory::getApplication();

		// Ensure that we know what was selected for the previous selector
		if (($n > 0 && !isset($selected_methods[$n - 1])) || ($n > 0 && $selected_methods[$n - 1] == 0))
		{
			$app->close();
		}

		// As a safety measure prevent more than 5 selectors and always allow only one more selector than already selected
		if ($n > 4 || $n > count($selected_methods) + 1)
		{
			$app->close();
		}

		// Reduce the translation methods offered depending on the parents
		if ($n > 0 && !empty($selected_methods))
		{
			$parent_method                   = $selected_methods[$n - 1];
			$acceptable_follow_up_method_ids = $translation_methods[$parent_method]->acceptable_follow_up_method_ids;
			$acceptable_follow_up_methods    = explode(',', $acceptable_follow_up_method_ids);

			foreach ($translation_methods as $k => $translation_method)
			{
				if (!in_array($k, $acceptable_follow_up_methods))
				{
					unset($translation_methods[$k]);
				}
			}
		}

		// If there are no translation methods left then return nothing
		if (!count($translation_methods))
		{
			JFactory::getApplication()->close();
		}

		/* @var $db NenoDatabaseDriverMysqlx */
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('setting_value')
			->from('#__neno_settings')
			->where('setting_key LIKE ' . $db->quote('translation_method_%'))
			->order('setting_key ASC');

		$db->setQuery($query);
		$translation_methods_selected = $db->loadArray();

		// Prepare display data
		$displayData                                 = array ();
		$displayData['translation_methods']          = $translation_methods;
		$displayData['assigned_translation_methods'] = $translation_methods_selected;
		$displayData['n']                            = $n;

		$selectorHTML = JLayoutHelper::render('translationmethodselector', $displayData, JPATH_NENO_LAYOUTS);

		echo $selectorHTML;

		$app->close();

	}

	/**
	 * Validate installation step 1
	 *
	 * @return bool
	 */
	protected function validateStep1()
	{
		$input          = $this->input;
		$sourceLanguage = $input->post->get('source_language');
		$app            = JFactory::getApplication();

		if (!empty($sourceLanguage))
		{
			$language           = JFactory::getLanguage();
			$knownLanguagesTags = array_keys($language->getKnownLanguages());

			if (!in_array($sourceLanguage, $knownLanguagesTags))
			{
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);

				$query
					->select('update_id')
					->from('#__updates')
					->where('element = ' . $db->quote('pkg_' . $sourceLanguage))
					->order('update_id DESC');

				$db->setQuery($query, 0, 1);
				$updateId = $db->loadResult();

				if (!empty($updateId))
				{
					if (!NenoHelper::installLanguage($updateId))
					{
						$app->enqueueMessage('There was an error install language. Please try again later.', 'error');

						return false;
					}
				}
			}

			// Once the language is installed, let's mark it as default
			JLoader::register('LanguagesModelInstalled', JPATH_ADMINISTRATOR . '/components/com_languages/models/installed.php');

			/* @var $model LanguagesModelInstalled */
			$model = JModelLegacy::getInstance('Installed', 'LanguagesModel');

			// If the language has been marked as default, let's save that on the settings
			if ($model->publish($sourceLanguage))
			{
				NenoSettings::set('source_language', $sourceLanguage, true);
			}

			return true;
		}

		$app->enqueueMessage('Error getting source language', 'error');

		return false;
	}

	/**
	 * Validate installation step 2
	 *
	 * @return bool
	 */
	protected function validateStep2()
	{
		$input       = $this->input;
		$tasksOption = $input->getWord('schedule_task_option');
		$app         = JFactory::getApplication();

		if (!empty($tasksOption))
		{
			// If the option selected is AJAX, let's enable the module
			if ($tasksOption === 'ajax')
			{
				// Do something
			}

			NenoSettings::set('schedule_task_option', $tasksOption);

			return true;
		}

		$app->enqueueMessage('COM_NENO_INSTALLATION_ERROR');

		return false;
	}

	/**
	 * Validate installation step 3
	 *
	 * @return bool
	 */
	protected function validateStep3()
	{
		$input = $this->input;
		$app   = JFactory::getApplication();

		$jform = $input->post->get('jform', array (), 'ARRAY');

		if (!empty($jform['translation_methods']))
		{
			foreach ($jform['translation_methods'] as $key => $translationMethod)
			{
				NenoSettings::set('translation_method_' . ($key + 1), $translationMethod);
			}

			return true;
		}

		$app->enqueueMessage('', 'error');

		return false;
	}
}
