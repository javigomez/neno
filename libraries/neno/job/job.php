<?php
/**
 * @package     Neno
 * @subpackage  Job
 *
 * @author      Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('JPATH_NENO') or die;

/**
 * Class NenoJob
 *
 * @since  1.0
 */
class NenoJob extends NenoObject
{
	/**
	 * Status when the job has been generated
	 */
	const JOB_STATUS_GENERATED = 1;

	/**
	 * Status when the job has been sent to the API Server
	 */
	const JOB_STATUS_SENT = 2;

	/**
	 * Status when the job has been completed
	 */
	const JOB_STATUS_COMPLETED = 3;

	/**
	 * @var integer
	 */
	protected $status;

	/**
	 * @var Datetime
	 */
	protected $createdTime;

	/**
	 * @var Datetime
	 */
	protected $sentTime;

	/**
	 * @var Datetime
	 */
	protected $completedTime;

	/**
	 * @var string
	 */
	protected $translationMethod;

	/**
	 * @var string
	 */
	protected $fromLanguage;

	/**
	 * @var string
	 */
	protected $toLanguage;

	/**
	 * @var array
	 */
	private $translations;

	/**
	 * Create a job file
	 *
	 * @return bool True on success
	 *
	 * @throws Exception If something happens when the zip file is being created.
	 */
	public function generateJobFile()
	{
		$strings  = array ();
		$filename = $this->getFileName();

		/* @var $translation NenoContentElementTranslation */
		foreach ($this->translations as $translation)
		{
			$strings[$translation->getId()] = $translation->getOriginalText();
		}

		$jobData = array (
			'id'                 => $this->getId(),
			'job_create_time'    => $this->getCreatedTime(true),
			'file_name'          => $filename,
			'translation_method' => $this->getTranslationMethod(),
			'from'               => $this->getFromLanguage(),
			'to'                 => $this->getToLanguage(),
			'strings'            => $strings
		);

		$config  = JFactory::getConfig();
		$tmpPath = $config->get('tmp_path');

		file_put_contents($tmpPath . '/' . $filename . 'json', json_encode($jobData));

		/* @var $zipArchiveAdapter JArchiveZip */
		$zipArchiveAdapter = JArchive::getAdapter('zip');
		$result            = $zipArchiveAdapter->create($tmpPath . '/' . $filename . 'json.zip', $tmpPath . '/' . $filename . 'json');

		// If something happens in the process of creating the job file, let's throw an exception
		if (!$result)
		{
			throw new Exception('Error creating job file');
		}

		return $result;
	}

	/**
	 * Generate filename for the job
	 *
	 * @return string
	 */
	protected function getFileName()
	{
		return strtolower($this->fromLanguage) . '-to-' . strtolower($this->toLanguage) . '-' . time() . '-' . JFactory::getUser()->id;
	}

	/**
	 * Get the Job Id
	 *
	 * @return string
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Get created date
	 *
	 * @param   bool   $formatted If the date should be formatted
	 * @param   string $format    Which format should be used
	 *
	 * @return Datetime|string
	 */
	public function getCreatedTime($formatted = false, $format = 'Y-m-d H:i:s')
	{
		if ($formatted)
		{
			return $this->createdTime->format($format);
		}
		else
		{
			return $this->createdTime;
		}
	}

	/**
	 * Get Translation method
	 *
	 * @return string
	 */
	public function getTranslationMethod()
	{
		return $this->translationMethod;
	}

	/**
	 * Get the language that the strings will be translate from
	 *
	 * @return string
	 */
	public function getFromLanguage()
	{
		return $this->fromLanguage;
	}

	/**
	 * Get the language that the strings will be translate to
	 *
	 * @return string
	 */
	public function getToLanguage()
	{
		return $this->toLanguage;
	}

	/**
	 * Get all the strings that needs to be translated.
	 *
	 * @return array
	 */
	public function getTranslations()
	{
		return $this->translations;
	}

	/**
	 * Get Job status
	 *
	 * @return int
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * Get the date when the job was sent
	 *
	 * @return Datetime
	 */
	public function getSentTime()
	{
		return $this->sentTime;
	}

	/**
	 * Get the date when the job was completed
	 *
	 * @return Datetime
	 */
	public function getCompletedTime()
	{
		return $this->completedTime;
	}

	/**
	 * Generate an id for a new record
	 *
	 * @return mixed
	 */
	public function generateId()
	{
		return NenoHelper::generateRandomString();
	}
}
