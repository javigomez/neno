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

		$sidebar = '';

		if ($step == 5)
		{
			NenoHelper::addSubmenu();
			$sidebar = JHtmlSidebar::render();
		}

		echo json_encode(array ('installation_step' => $layout, 'jsidebar' => $sidebar));

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
				$languages           = NenoHelper::findLanguages(true);
				$data->select_widget = JHtml::_('select.genericlist', $languages, 'source_language', null, 'iso', 'name', NenoSettings::get('source_language'));
				break;
			case 3:
				$language                   = JFactory::getLanguage();
				$default                    = NenoSettings::get('source_language');
				$knownLanguages             = $language->getKnownLanguages();
				$languagesData              = array ();
				$defaultTranslationsMethods = NenoHelper::getDefaultTranslationMethods();
				$db                         = JFactory::getDbo();
				$query                      = $db->getQuery(true);
				$query
					->insert('#__neno_content_language_defaults')
					->columns(
						array (
							'lang',
							'translation_method_id',
							'ordering'
						)
					);

				$insert = false;

				foreach ($knownLanguages as $key => $knownLanguage)
				{
					if ($knownLanguage['tag'] != $default)
					{
						$insert                                    = true;
						$languagesData[$key]                       = $knownLanguage;
						$languagesData[$key]['lang_code']          = $knownLanguage['tag'];
						$languagesData[$key]['title']              = $knownLanguage['name'];
						$languagesData[$key]['translationMethods'] = $defaultTranslationsMethods;
						$languagesData[$key]['errors']             = NenoHelper::getLanguageErrors($languagesData[$key]);
						$languagesData[$key]['placement']          = 'installation';
						$languagesData[$key]['image']              = NenoHelper::getLanguageImage($knownLanguage['tag']);
						$languagesData[$key]['published']          = NenoHelper::isLanguagePublished($knownLanguage['tag']);

						foreach ($defaultTranslationsMethods as $ordering => $defaultTranslationsMethod)
						{
							$query->values($db->quote($knownLanguage['tag'] . ',' . $defaultTranslationsMethod->id . ',' . ($ordering + 1)));
						}
					}
				}

				if ($insert)
				{
					$db->setQuery($query);
					$db->execute();
				}

				$data->languages           = $languagesData;
				$data->canInstallLanguages = true;
				$memoryDetails             = NenoHelper::getMemoryDetails();

				if (!empty($memoryDetails))
				{
					$data->canInstallLanguages = $memoryDetails['free_space'] == 0 || $memoryDetails['free_space'] > $memoryDetails['current_data_space'];
				}

				break;
			case 4:
				/* @var $db NenoDatabaseDriverMysqlx */
				$db            = JFactory::getDbo();
				$query         = $db->getQuery(true);
				$tablesIgnored = NenoHelper::getDoNotTranslateTables();

				/* @var $config \Joomla\Registry\Registry */
				$config = JFactory::getConfig();

				$query
					->select('DISTINCT TABLE_NAME')
					->from('INFORMATION_SCHEMA.COLUMNS')
					->where(
						array (
							'COLUMN_NAME = ' . $db->quote('language'),
							'TABLE_SCHEMA = ' . $db->quote($config->get('db')),
							'TABLE_NAME NOT LIKE ' . $db->quote('%neno%'),
							'TABLE_NAME NOT LIKE ' . $db->quote('%\_\_%'),
							'TABLE_NAME NOT LIKE ' . $db->quote('%menu'),
						)
					);

				$db->setQuery($query);
				$tables = $db->loadArray();

				$tablesFound = array ();

				foreach ($tables as $table)
				{
					if (!in_array(str_replace($db->getPrefix(), '#__', $table), $tablesIgnored))
					{
						$sourceLanguage      = NenoSettings::get('source_language');
						$sourceLanguageParts = explode('-', $sourceLanguage);
						$query
							->clear()
							->select(
								array (
									'COUNT(*) AS counter',
									'language',
									$db->quote($table) . ' AS `table`'
								)
							)
							->from($db->quoteName($table))
							->where(
								array (
									'language <> ' . $db->quote('*'),
									'language <> ' . $db->quote(''),
									'language <> ' . $db->quote($sourceLanguage),
									'language <> ' . $db->quote($sourceLanguageParts[0]),
								)
							)
							->group('language');

						$db->setQuery($query);
						$recordsFound = $db->loadObjectList();

						if (!empty($recordsFound))
						{
							$tablesFound = array_merge($tablesFound, $recordsFound);
						}
					}
				}

				$data->tablesFound = $tablesFound;
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

	/**
	 * Get previous messages
	 *
	 * @return void
	 */
	public function getPreviousMessages()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('*')
			->from('#__neno_installation_messages as m1')
			->where(
				array (
					'm1.fetched = 1'
				)
			)
			->group('level')
			->order(
				array (
					'level ASC',
					'id DESC'
				)
			);

		$db->setQuery($query);
		echo json_encode($db->loadAssocList());

		JFactory::getApplication()->close();
	}

	/**
	 * Execute discovering process
	 *
	 * @return void
	 */
	public function processDiscoveringStep()
	{
		// Define installation flag
		define('NENO_INSTALLATION', 1);

		/* @var $db NenoDatabaseDriverMysqlx */
		$db       = JFactory::getDbo();
		$query    = $db->getQuery(true);
		$finished = NenoSettings::get('installation_completed') == 1;

		// Do until timeout
		while (!$finished)
		{
			// Check if the menus have been created
			if (NenoSettings::get('discovering_extensions') != 1) // Check if the extensions have been discovered
			{
				// Check if there was a process executing before
				if (NenoSettings::get('discovering_field') != null)
				{
					/* @var $field NenoContentElementField */
					$field = NenoContentElementField::load(NenoSettings::get('discovering_field'), false, true);

					if (!empty($field))
					{
						$field->persistTranslations();
					}

					NenoSettings::set('discovering_field', null);
				}
				elseif (NenoSettings::get('discovering_languagestring') != null)
				{
					if (NenoSettings::get('discovering_languagestring') != '')
					{
						/* @var $languageString NenoContentElementLanguageString */
						$languageString = NenoContentElementLanguageString::load(NenoSettings::get('discovering_languagestring'), false, true);

						if (!empty($languageString))
						{
							$languageString->persist();
						}
					}

					NenoSettings::set('discovering_languagestring', null);
				}
				elseif (NenoSettings::get('discovering_table') != null)
				{
					/* @var $table NenoContentElementTable */
					$table = NenoContentElementTable::load(NenoSettings::get('discovering_table'), false, true);

					if (!empty($table))
					{
						$table->getFields(false, true, true);
						$table->persist();
					}

					NenoSettings::get('discovering_table', null);
				}
				elseif (NenoSettings::get('discovering_languagefile') != null)
				{
					/* @var $languageFile NenoContentElementLanguageFile */
					$languageFile = NenoContentElementLanguageFile::load(NenoSettings::get('discovering_languagefile'), false, true);

					if (!empty($languageFile))
					{
						$languageFile->loadStringsFromFile(true);
						$languageFile->persist();
					}

					NenoSettings::get('discovering_languagefile', null);
				}
				elseif (NenoSettings::get('discovering_group') != null)
				{
					/* @var $group NenoContentElementGroup */
					$group = NenoContentElementGroup::load(NenoSettings::get('discovering_group'));

					if (!empty($group))
					{
						$group->getTables(false, true);
						$group->getLanguageFiles();
						$group->persist();
					}
				}
				else
				{
					// If it's not, let's get which extensions haven't been discovered yet
					$extensions = $db->quote(NenoHelper::whichExtensionsShouldBeTranslated());

					$query
						->clear()
						->select('e.*')
						->from('`#__extensions` AS e')
						->where(
							array (
								'e.type IN (' . implode(',', $extensions) . ')',
								'e.name NOT LIKE \'com_neno\'',
								'NOT EXISTS (SELECT 1 FROM #__neno_content_element_groups_x_extensions AS ge WHERE ge.extension_id = e.extension_id)'
							)
						)
						->order('name');
					$db->setQuery($query, 0, 1);
					$extension = $db->loadAssoc();

					// There's no extensions to be discovered
					if (empty($extension))
					{
						NenoSettings::set('discovering_extensions', 1);
					}
					else
					{
						NenoHelper::discoverExtension($extension);
					}
				}
			}
			elseif (NenoSettings::get('parsing_others') != 1) // Check if other tables have been grouped
			{
				NenoHelper::setSetupState(95, JText::_('COM_NENO_INSTALLATION_MESSAGE_PARSING_OTHER_TABLES'));
				NenoHelper::groupingTablesNotDiscovered();
				NenoSettings::set('parsing_others', 1);
			}
			elseif (NenoSettings::get('do_not_translate_group') != 1) // Check if DoNotTranslate group has been created
			{
				NenoHelper::createDoNotTranslateGroup();
				NenoSettings::set('do_not_translate_group', 1);
			}
			elseif (NenoSettings::get('publishing_plugins') != 1) // Check if plugins have been enabled
			{
				$query
					->clear()
					->update('#__extensions')
					->set('enabled = 1')
					->where(
						array (
							'element LIKE ' . $db->quote('languagecode'),
							'element LIKE ' . $db->quote('languagefilter')
						), 'OR'
					);

				$db->setQuery($query);
				$db->execute();

				NenoSettings::set('publishing_plugins', 1);
			}
			elseif (NenoSettings::get('discovering_step_menu') != 1)
			{
				NenoHelper::setSetupState(0, JText::_('COM_NENO_INSTALLATION_MESSAGE_GENERATING_MENUS'));
				NenoHelper::createMenuStructure();
				NenoSettings::set('discovering_step_menu', 1);

				// Calculate percent per extension discovered
				$extensions = $db->quote(NenoHelper::whichExtensionsShouldBeTranslated());
				$query
					->clear()
					->select('COUNT(*)')
					->from('`#__extensions` AS e')
					->where(
						array (
							'e.type IN (' . implode(',', $extensions) . ')',
							'e.name NOT LIKE \'com_neno\''
						)
					);

				$db->setQuery($query);
				$extensionToDiscover = (int) $db->loadResult();

				$percentPerExtension = 85 / $extensionToDiscover;
				NenoSettings::set('percentPerExtension', $percentPerExtension);
			}
			else
			{
				NenoHelper::setSetupState(100, JText::_('COM_NENO_INSTALLATION_MESSAGE_INSTALLATION_COMPLETED'));

				// Set installation as completed
				NenoSettings::set('installation_completed', 1);
				$finished = true;
			}
		}

		if ($finished)
		{
			echo 'ok';
		}

		JFactory::getApplication()->close();
	}

	/**
	 * Fetch setup status
	 *
	 * @return void
	 */
	public function getSetupStatus()
	{
		$setupState = NenoHelper::getSetupState();
		echo json_encode($setupState);
		JFactory::getApplication()->close();
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
	 * Validate installation step 3
	 *
	 * @return bool
	 */
	protected function validateStep2()
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
