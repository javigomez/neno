<?php
/**
 * @package     Neno
 * @subpackage  Controller
 *
 * @author      Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('JPATH_NENO') or die;

/**
 * Class NenoController
 *
 * @since  1.0
 */
class NenoController extends JControllerLegacy
{
	/**
	 * Process task queue
	 *
	 * @return void
	 */
	public function processTaskQueue()
	{
		NenoTaskMonitor::runTask(1);
		JFactory::getApplication()->close();
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param   boolean $cachable  If Joomla should cache the response
	 * @param   array   $urlParams URL parameters
	 *
	 * @return JController
	 */
	public function display($cachable = false, $urlParams = array ())
	{
		$input = $this->input;
		$view  = $input->getCmd('view', 'dashboard');
		$app   = JFactory::getApplication();

		if (NenoSettings::get('installation_completed') != 1 && NenoSettings::get('installation_status') != 6 && $view != 'installation' && $view != 'debug' && $app->isAdmin())
		{
			if ($view != 'dashboard')
			{
				$app->enqueueMessage(JText::_('COM_NENO_INSTALLATION_ERROR'), 'error');
			}

			$app->redirect('index.php?option=com_neno&view=installation');
		}

		$input->set('view', $view);

		// Ensure that a working language is set for some views
		$viewsThatRequireWorkingLanguage = array (
			'groupselements', 'editor', 'strings'
		);

		$showLanguagesDropDown = false;

		if (in_array($view, $viewsThatRequireWorkingLanguage))
		{
			// Get working language
			$workingLanguage       = NenoHelper::getWorkingLanguage();
			$languages             = JFactory::getLanguage()->getKnownLanguages();
			$showLanguagesDropDown = true;

			if (empty($workingLanguage) || !in_array($workingLanguage, array_keys($languages)))
			{
				$url = JRoute::_('index.php?option=com_neno&view=setworkinglang&next=' . $view, false);
				$this->setRedirect($url);
				$this->redirect();
			}
		}

		NenoHelper::setAdminTitle($showLanguagesDropDown);

		parent::display($cachable, $urlParams);

		return $this;
	}

	/**
	 * Check if the user has lost the session
	 *
	 * @return void
	 */
	public function checkSession()
	{
		if (!JFactory::getUser()->guest)
		{
			echo 'ok';
		}

		JFactory::getApplication()->close();
	}

	/**
	 * Set working language
	 *
	 * @return void
	 */
	public function setWorkingLang()
	{
		$lang = JFactory::getApplication()->input->getString('lang', '');
		$next = JFactory::getApplication()->input->getString('next', 'dashboard');

		NenoHelper::setWorkingLanguage($lang);

		$url = JRoute::_('index.php?option=com_neno&view=' . $next, false);
		$this->setRedirect($url);
		$this->redirect();
	}

	/**
	 * Set a translation as ready
	 *
	 * @return void
	 */
	public function translationReady()
	{
		$input = $this->input;
		$jobId = $input->get->getString('jobId');

		/* @var $job NenoJob */
		$job = NenoJob::load($jobId);

		if ($job === null)
		{
			NenoLog::add('Job not found. Job Id:' . $jobId, NenoLog::PRIORITY_ERROR);
		}
		else
		{
			// Set the job as completed by the server but the component hasn't processed it yet.
			$job
				->setState(NenoJob::JOB_STATE_COMPLETED)
				->persist();

			// Create task into the queue
			NenoTaskMonitor::addTask('job_fetcher');

			echo 'ok';
		}

		JFactory::getApplication()->close();
	}

	/**
	 * Fix Language issue
	 *
	 * @return void
	 */
	public function fixLanguageIssue()
	{
		$input    = $this->input;
		$language = $input->post->getString('language');
		$issue    = $input->post->getCmd('issue');

		if (NenoHelper::fixLanguageIssues($language, $issue) === true)
		{
			echo 'ok';
		}
		else
		{
			echo 'err';
		}

		JFactory::getApplication()->close();
	}

	/**
	 * Show languages modal content
	 *
	 * @return void
	 */
	public function showInstallLanguagesModal()
	{
		$languages = NenoHelper::findLanguages();
		$placement = $this->input->getString('placement', 'dashboard');

		if (!empty($languages))
		{
			$displayData            = new stdClass;
			$displayData->languages = $languages;
			$displayData->placement = $placement;
			echo JLayoutHelper::render('installlanguages', $displayData, JPATH_NENO_LAYOUTS);
		}
		else
		{
			echo JText::_('COM_NENO_INSTALL_LANGUAGES_NO_LANGUAGES_TO_INSTALL');
		}

		JFactory::getApplication()->close();
	}

	/**
	 * Install language
	 *
	 * @return void
	 */
	public function installLanguage()
	{
		$input     = $this->input;
		$updateId  = $input->post->getInt('update');
		$language  = $input->post->getString('language');
		$placement = $input->post->getCmd('placement');

		if (NenoHelper::installLanguage($updateId, $placement != 'dashboard'))
		{
			/* @var $db NenoDatabaseDriverMysqlx */
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query
				->select(
					array (
						'l.lang_code',
						'l.published',
						'l.title',
						'l.image',
						'tr.state',
						'SUM(tr.word_counter) AS word_count'
					)
				)
				->from('#__languages AS l')
				->leftJoin('#__neno_content_element_translations AS tr ON tr.language = l.lang_code')
				->where('l.lang_code = ' . $db->quote($language))
				->group(
					array (
						'l.lang_code',
						'tr.state'
					)
				)
				->order('lang_code');

			$db->setQuery($query);
			$languages = $db->loadObjectListMultiIndex('lang_code');
			$item      = new stdClass;

			foreach ($languages as $language)
			{
				$translated               = 0;
				$queued                   = 0;
				$changed                  = 0;
				$untranslated             = 0;
				$item->lang_code          = $language[0]->lang_code;
				$item->published          = $language[0]->published;
				$item->title              = $language[0]->title;
				$item->image              = $language[0]->image;
				$item->errors             = NenoHelper::getLanguageErrors((array) $language[0]);
				$item->translationMethods = NenoHelper::getLanguageDefault($item->lang_code);

				// If the language was installed from the dashboard, let's add a task to set all the shadow tables structure
				if ($placement == 'dashboard')
				{
					NenoTaskMonitor::addTask('language', array ('language' => $item->lang_code));
				}
				
				$item->isInstalled = NenoHelper::isCompletelyInstall($language[0]->lang_code);

				foreach ($language as $internalItem)
				{
					switch ($internalItem->state)
					{
						case NenoContentElementTranslation::TRANSLATED_STATE:
							$untranslated = (int) $internalItem->word_count;
							break;
						case NenoContentElementTranslation::QUEUED_FOR_BEING_TRANSLATED_STATE:
							$untranslated = (int) $internalItem->word_count;
							break;
						case NenoContentElementTranslation::SOURCE_CHANGED_STATE:
							$untranslated = (int) $internalItem->word_count;
							break;
						case NenoContentElementTranslation::NOT_TRANSLATED_STATE:
							$untranslated = (int) $internalItem->word_count;
							break;
					}
				}

				$item->wordCount               = new stdClass;
				$item->wordCount->translated   = $translated;
				$item->wordCount->queued       = $queued;
				$item->wordCount->changed      = $changed;
				$item->wordCount->untranslated = $untranslated;
				$item->wordCount->total        = $translated + $queued + $changed + $untranslated;
				$item->placement               = $placement;
			}

			echo JLayoutHelper::render('languageconfiguration', get_object_vars($item), JPATH_NENO_LAYOUTS);
		}
		else
		{
			echo 'err';
		}

		JFactory::getApplication()->close();
	}

	/**
	 * Install language
	 *
	 * @return void
	 */
	public function removeLanguage()
	{
		$input    = $this->input;
		$language = $input->getString('language');

		if (NenoHelper::deleteLanguage($language))
		{
			echo 'ok';
		}
		else
		{
			echo 'err';
		}

		JFactory::getApplication()->close();
	}

	/**
	 * Get translation method selector
	 *
	 * @return void
	 */
	public function getTranslationMethodSelector()
	{
		$input              = $this->input;
		$n                  = $input->getInt('n', 0);
		$selected_methods   = $input->get('selected_methods', array (), 'ARRAY');
		$placement          = $input->getString('placement', 'general');
		$translationMethods = NenoHelper::loadTranslationMethods();
		$app                = JFactory::getApplication();

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
			$acceptable_follow_up_method_ids = $translationMethods[$parent_method]->acceptable_follow_up_method_ids;
			$acceptable_follow_up_methods    = explode(',', $acceptable_follow_up_method_ids);

			foreach ($translationMethods as $k => $translation_method)
			{
				if (!in_array($k, $acceptable_follow_up_methods))
				{
					unset($translationMethods[$k]);
				}
			}
		}

		// If there are no translation methods left then return nothing
		if (!count($translationMethods))
		{
			JFactory::getApplication()->close();
		}

		// Prepare display data
		$displayData                        = array ();
		$displayData['translation_methods'] = $translationMethods;
		$displayData['n']                   = $n;

		if ($placement == 'general')
		{
			$displayData['assigned_translation_methods'] = NenoHelper::getTranslationMethods('dropdown');
		}
		else
		{
			$lang                                        = $input->getString('language');
			$displayData['assigned_translation_methods'] = NenoHelper::getLanguageDefault($lang, $n);
		}

		$selectorHTML = JLayoutHelper::render('translationmethodselector', $displayData, JPATH_NENO_LAYOUTS);

		echo $selectorHTML;

		$app->close();
	}

	/**
	 * Save translation method
	 *
	 * @return void
	 */
	public function saveTranslationMethod()
	{
		$input             = $this->input;
		$language          = $input->getString('language');
		$translationMethod = $input->getInt('translationMethod');
		$ordering          = $input->getInt('ordering');

		if (!empty($language))
		{
			/* @var $db NenoDatabaseDriverMysqlx */
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query
				->delete('#__neno_content_language_defaults')
				->where(
					array (
						'lang = ' . $db->quote($language),
						'ordering >= ' . $ordering
					)
				);

			$db->setQuery($query);
			$db->execute();

			$query
				->clear()
				->insert('#__neno_content_language_defaults')
				->columns(
					array (
						'lang',
						'translation_method_id',
						'ordering'
					)
				)
				->values($db->quote($language) . ',' . $translationMethod . ',' . $ordering);
			$db->setQuery($query);
			$db->execute();


			JFactory::getApplication()->close();
		}
	}

	public function createMenus()
	{
		NenoHelper::createMenuStructure();
	}
}
