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
 * Class NenoTaskWorkerJobScanner
 *
 * @since  1.0
 */
class NenoTaskWorkerJobScanner extends NenoTaskWorker
{
	/**
	 * Execute the task
	 *
	 * @param   array $taskData Task data
	 *
	 * @return bool True on success, false otherwise
	 */
	public function run($taskData)
	{
		$languages       = NenoHelper::getLanguages();
		$defaultLanguage = NenoSettings::get('source_language');
		$profiler        = new JProfiler;

		foreach ($languages as $language)
		{
			if ($language->lang_code !== $defaultLanguage)
			{
				$profiler->mark('Before create job' . $language->lang_code . ' Method: Machine');
				$machineJob = NenoJob::createJob($language->lang_code, NenoContentElementTranslation::MACHINE_TRANSLATION_METHOD);
				$profiler->mark('After create job' . $language->lang_code . ' Method: Machine');

				// If there are translations for this language and for this translation method
				if ($machineJob !== null)
				{
					NenoLog::add(count($machineJob->getTranslations()) . ' translations have been found to translate through machine translation');
				}

				$proJob = NenoJob::createJob($language->lang_code, NenoContentElementTranslation::PROFESSIONAL_TRANSLATION_METHOD);

				// If there are translations for this language and for this translation method
				if ($proJob !== null)
				{
					NenoLog::add(count($proJob->getTranslations()) . ' translations have been found to translate through professional translation');
				}

				if ($machineJob !== null || $proJob !== null)
				{
					NenoTaskMonitor::addTask('job_sender');
				}
			}
		}
	}
}
