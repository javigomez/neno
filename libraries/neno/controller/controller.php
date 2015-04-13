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
			'groupselements'
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

			echo 'ok';
		}

		JFactory::getApplication()->close();
	}
}
