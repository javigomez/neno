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
 * Class NenoTaskWorkerJobSender
 */
class NenoTaskWorkerJobSender extends NenoTaskWorker
{
	/**
	 * Execute the task
	 *
	 * @param   array $taskData Task data
	 *
	 * @return bool True on success, false otherwise
	 */
	public function run(array $taskData)
	{
		$jobs = NenoJob::load(array ());

		// If it's not an array, let's convert to that
		if (!is_array($jobs))
		{
			$jobs = array ($jobs);
		}

		/* @var $job NenoJob */
		foreach ($jobs as $job)
		{
			$job->generateJobFile();
			$job
				->setSentTime(new DateTime)
				->persist();

			// Send API call to the server to fetch the file
		}
	}
}
