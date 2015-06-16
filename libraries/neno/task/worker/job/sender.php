<?php
/**
 * @package     Neno
 * @subpackage  Task
 *
 * @copyright   Copyright (c) 2014 Jensen Technologies S.L. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Class NenoTaskWorkerJobSender
 *
 * @since  1.0
 */
class NenoTaskWorkerJobSender extends NenoTaskWorker
{
	/**
	 * Execute the task
	 *
	 * @param   array $taskData Task data
	 *
	 * @return bool True on success, false otherwise
	 *
	 * @throws Exception
	 */
	public function run($taskData)
	{
		$jobs = NenoJob::load(
			array (
				'_order' => array (
					'created_time' => 'ASC'
				),
				'_limit' => 1
			)
		);

		// If it's not an array, let's convert to that
		if (!is_array($jobs))
		{
			$jobs = array ($jobs);
		}

		/* @var $job NenoJob */
		foreach ($jobs as $job)
		{
			$job->sendJob();
		}
	}
}
