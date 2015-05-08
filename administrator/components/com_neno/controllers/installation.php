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
			case 3:
				$translation_methods = NenoHelper::loadTranslationMethods();
				$layoutData          = array ('translation_methods' => $translation_methods);
				$data->select_widget = JLayoutHelper::render('translationmethodselector', $layoutData, JPATH_NENO_LAYOUTS);
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

			$model->publish($sourceLanguage);

			return true;
		}

		$app->enqueueMessage('Error getting source language', 'error');

		return false;
	}
}
