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
	public function run(array $taskData)
	{
		$languages       = NenoHelper::getLanguages();
		$defaultLanguage = JFactory::getLanguage()->getDefault();

		foreach ($languages as $language)
		{
			if ($language !== $defaultLanguage)
			{
				$machineJob = NenoJob::createJob($language, NenoContentElementTranslation::MACHINE_TRANSLATION_METHOD);

				// If there are translations for this language and for this translation method
				if ($machineJob !== null)
				{
					NenoLog::add(count($machineJob->getTranslations()) . ' translations have been found to translate through machine translation');
				}

				$proJob = NenoJob::createJob($language, NenoContentElementTranslation::PROFESSIONAL_TRANSLATION_METHOD);

				// If there are translations for this language and for this translation method
				if ($proJob)
				{
					NenoLog::add(count($proJob->getTranslations()) . ' translations have been found to translate through professional translation');
				}
			}
		}

	}
}
