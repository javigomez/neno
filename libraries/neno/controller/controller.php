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
		NenoTaskMonitor::runTask();
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
			$showLanguagesDropDown = true;

			if (empty($workingLanguage))
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

		if (NenoHelper::installLanguage($updateId))
		{
			// Create a new query object.
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
			$languageData              = $db->loadAssoc();
			$languageData['placement'] = $placement;

			echo JLayoutHelper::render('languageconfiguration', $languageData, JPATH_NENO_LAYOUTS);
		}
		else
		{
			echo 'err';
		}

		JFactory::getApplication()->close();
	}
}
