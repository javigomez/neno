<?php

/**
 * @package     Neno
 * @subpackage  Task
 *
 * @copyright   Copyright (c) 2014 Jensen Technologies S.L. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('JPATH_NENO') or die;

/**
 * Class NenoTaskWorkerJobFetcher
 */
class NenoTaskWorkerJobFetcher extends NenoTaskWorker
{
	/**
	 * Execute the task
	 *
	 * @param   array|null $taskData Task data
	 *
	 * @return bool True on success, false otherwise
	 */
	public function run($taskData)
	{
		$jobs = NenoJob::load(array ('state' => NenoJob::JOB_STATE_COMPLETED));

		// If there is only one job, let's transform it to an array
		if (!is_array($jobs))
		{
			$jobs = array ($jobs);
		}

		/* @var $job NenoJob */
		foreach ($jobs as $job)
		{
			if ($job->fetchJobFromServer() === true)
			{
				if ($job->processJobFinished() === true)
				{
					$job
						->setState(NenoJob::JOB_STATE_PROCESSED)
						->persist();

					NenoLog::add('Job #' . $job->getId() . ' has been successfully processed.');
				}
				else
				{
					NenoLog::add('There as an error reading the content of the file.', NenoLog::PRIORITY_ERROR);
				}
			}
			else
			{
				NenoLog::add('There was an error fetching the file from the API server', NenoLog::PRIORITY_ERROR);
			}
		}
	}
}
