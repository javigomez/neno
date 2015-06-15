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
			$job->generateJobFile();
			$job
				->setSentTime(new DateTime)
				->setState(NenoJob::JOB_STATE_SENT);

			$data = json_encode(
				array (
					'filename'             => $job->getFileName() . '.zip',
					'words'                => $job->getWordCount(),
					'translation_method'   => $job->getTranslationMethod(),
					'source_language'      => $job->getFromLanguage(),
					'destination_language' => $job->getToLanguage()
				)
			);

			list($status, $response) = NenoHelperApi::makeApiCall('job', 'POST', $data);

			if ($status === false)
			{
				$job
					->setSentTime(null)
					->setState(NenoJob::JOB_STATE_GENERATED);

				if ($response['code'] == 402)
				{
					$job->setState(NenoJob::JOB_STATE_NO_TC);
				}
			}

			$job->persist();
		}
	}
}
