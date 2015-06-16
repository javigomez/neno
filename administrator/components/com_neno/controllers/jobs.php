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
}
