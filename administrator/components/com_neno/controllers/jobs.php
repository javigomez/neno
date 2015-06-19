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
class NenoControllerJobs extends JControllerAdmin
{
	/**
	 * Resend job
	 *
	 * @return void
	 */
	public function resend()
	{
		$app   = JFactory::getApplication();
		$input = $app->input;
		$jobId = $input->getInt('jobId');

		/* @var $job NenoJob */
		$job = NenoJob::load($jobId, false, true);

		if (!empty($job))
		{
			if ($job->sendJob())
			{
				$app->enqueueMessage(JText::sprintf('COM_NENO_JOBS_JOB_SENT_SUCCESSFULLY', $job->getId()));
			}
			else
			{
				$app->enqueueMessage(JText::sprintf('COM_NENO_JOBS_JOB_SENT_ERROR', $job->getId()), 'error');
			}
		}

		$app->redirect('index.php?option=com_neno&view=jobs');
	}

	/**
	 * Fetch job file from server
	 *
	 * @return void
	 */
	public function fetch()
	{
		$app   = JFactory::getApplication();
		$input = $app->input;
		$jobId = $input->getInt('jobId');

		/* @var $job NenoJob */
		$job = NenoJob::load($jobId, false, true);

		if ($job->fetchJobFromServer() === true)
		{
			if ($job->processJobFinished() === true)
			{
				$job
					->setState(NenoJob::JOB_STATE_PROCESSED)
					->persist();

				$app->enqueueMessage('Job #' . $job->getId() . ' has been successfully processed.');
			}
			else
			{
				$app->enqueueMessage('There as an error reading the content of the file.', 'error');
			}
		}
		else
		{
			$app->enqueueMessage('There was an error fetching the file from the API server', 'error');
		}

		$app->redirect('index.php?option=com_neno&view=jobs');
	}
}
