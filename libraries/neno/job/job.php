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
	const JOB_STATE_GENERATED = 1;

	/**
	 * Status when the job has been sent to the API Server
	 */
	const JOB_STATE_SENT = 2;

	/**
	 * Status when the job has been completed by the API server
	 */
	const JOB_STATE_COMPLETED = 3;

	/**
	 * Status when the job has been processed by the component
	 */
	const JOB_STATE_PROCESSED = 4;

	/**
	 * @var integer
	 */
	protected $state;

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
	 * @var string
	 */
	protected $fileName;

	/**
	 * @var int
	 */
	private $wordCount;

	/**
	 * @var array
	 */
	private $translations;

	/**
	 * Constructor
	 *
	 * @param   mixed $data Data
	 */
	public function __construct($data)
	{
		parent::__construct($data);

		if (is_string($this->createdTime))
		{
			$this->createdTime = new DateTime($this->createdTime);
		}

		if (!$this->isNew())
		{
			/* @var $db NenoDatabaseDriverMysqlx */
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->select(
					array (
						't.id',
						't.content_type',
						't.content_id'
					)
				)
				->from('`#__neno_jobs_x_translations` AS jt')
				->innerJoin('`#__neno_content_element_translations` AS t ON jt.translation_id = t.id')
				->where('job_id = ' . $this->getId());
			$db->setQuery($query);
			$translations       = $db->loadAssocList();
			$this->translations = array ();
			$this->wordCount    = 0;

			foreach ($translations as $translation)
			{
				$translationOriginalText                = NenoHelper::getTranslationOriginalText(
					$translation['id'],
					$translation['content_type'],
					$translation['content_id']
				);
				$this->translations[$translation['id']] = $translationOriginalText;
				$this->wordCount                        = $this->wordCount + str_word_count($translationOriginalText);
			}
		}
	}

	/**
	 * Find a job and creates it.
	 *
	 * @param   string $toLanguage        JISO Language format
	 * @param   string $translationMethod Translation Method chosen
	 *
	 * @return NenoJob|null It will return a NenoJob object if there are translations pending or null if there aren't any.
	 */
	public static function createJob($toLanguage, $translationMethod)
	{
		// Load all the translations that need to be translated
		$translationObjects = NenoContentElementTranslation::load(
			array (
				'_select'            => array (
					'id'
				),
				'language'           => $toLanguage,
				'state'              => NenoContentElementTranslation::NOT_TRANSLATED_STATE,
				'translation_method' => $translationMethod,
				'_limit'             => 1000
			)
		);

		// If there is just one translation, let's convert it into an array
		if (!is_array($translationObjects))
		{
			$translationObjects = array ($translationObjects);
		}

		$job = null;

		if (!empty($translationObjects))
		{
			$jobData = array (
				'fromLanguage'      => JFactory::getLanguage()->getDefault(),
				'toLanguage'        => $toLanguage,
				'state'             => self::JOB_STATE_GENERATED,
				'createdTime'       => new DateTime,
				'translationMethod' => $translationMethod
			);

			$job = new NenoJob($jobData);
			$job
				->setTranslations(NenoHelper::convertOnePropertyArrayToSingleArray($translationObjects))
				->persist();
		}

		return $job;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return bool
	 */
	public function persist()
	{
		// If the job has been persisted properly, let's save the translations
		if (parent::persist())
		{
			$db = JFactory::getDbo();
			/* @var $query NenoDatabaseQueryMysqli */
			$query = $db->getQuery(true);

			$query
				->replace('#__neno_jobs_x_translations')
				->columns(
					array (
						'job_id',
						'translation_id'
					)
				);

			foreach ($this->translations as $translation)
			{
				/* @var $translation NenoContentElementTranslation */
				if ($translation instanceof NenoContentElementTranslation)
				{
					$query->values($db->quote($this->getId()) . ',' . $translation->getId());
				}
				else
				{
					$query->values($db->quote($this->getId()) . ',' . (int) $translation);
				}
			}

			$db->setQuery($query);

			return $db->execute() !== false;
		}

		return false;
	}

	/**
	 * Create a job file
	 *
	 * @return bool True on success
	 *
	 * @throws Exception If something happens when the zip file is being created.
	 */
	public function generateJobFile()
	{
		$filename = $this->getFileName();

		$jobData = array (
			'jobId'              => $this->getId(),
			'job_create_time'    => $this->getCreatedTime(true),
			'file_name'          => $filename,
			'translation_method' => $this->getTranslationMethod(),
			'from'               => $this->getFromLanguage(),
			'to'                 => $this->getToLanguage(),
			'strings'            => $this->translations
		);

		$config  = JFactory::getConfig();
		$tmpPath = $config->get('tmp_path');

		$fileData = array (
			'name' => $filename . '.json',
			'data' => json_encode($jobData)
		);

		/* @var $zipArchiveAdapter JArchiveZip */
		$zipArchiveAdapter = JArchive::getAdapter('zip');
		$result            = $zipArchiveAdapter->create($tmpPath . '/' . $filename . '.json.zip', array ($fileData));

		$this->fileName = $filename;

		// If something happens in the process of creating the job file, let's throw an exception
		if (!$result)
		{
			throw new Exception('Error creating job file');
		}

		$this->persist();

		return $result;
	}

	/**
	 * Generate filename for the job
	 *
	 * @return string
	 */
	public function getFileName()
	{
		return strtolower($this->fromLanguage) . '-to-' . strtolower($this->toLanguage) . '-' . $this->getId();
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
	 * Get how many word this job has
	 *
	 * @return int
	 */
	public function getWordCount()
	{
		return $this->wordCount;
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
	 * Set translations
	 *
	 * @param   array $translations Translations
	 *
	 * @return $this
	 */
	public function setTranslations(array $translations)
	{
		$this->translations = $translations;

		return $this;
	}

	/**
	 * Get Job status
	 *
	 * @return int
	 */
	public function getStatus()
	{
		return $this->state;
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
	 * Set sent time
	 *
	 * @param   Datetime $sentTime Time when the job has been sent
	 *
	 * @return $this
	 */
	public function setSentTime($sentTime)
	{
		$this->sentTime = $sentTime;

		return $this;
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
	 * Set completed time
	 *
	 * @param   Datetime $completedTime Time when the job has been completed
	 *
	 * @return $this
	 */
	public function setCompletedTime(Datetime $completedTime)
	{
		$this->completedTime = $completedTime;

		return $this;
	}

	/**
	 * Set State
	 *
	 * @param   int $state State
	 *
	 * @return $this
	 */
	public function setState($state)
	{
		$this->state = $state;

		return $this;
	}

	/**
	 * Fetch the job file from the server
	 *
	 * @return bool|JError True on Success or false|JError if something goes wrong.
	 */
	public function fetchJobFromServer()
	{
		$config   = JFactory::getConfig();
		$tmpPath  = $config->get('tmp_path');
		$filename = $this->getFileName();

		/* @var $zipAdapter JArchiveZip */
		$zipAdapter = JArchive::getAdapter('zip');

		try
		{
			return $zipAdapter->extract('http://localhost/neno-translate/tmp/' . $filename . '.json.zip', $tmpPath . '/' . $filename);
		}
		catch (RuntimeException $e)
		{
			return false;
		}
	}

	/**
	 * Process a file
	 *
	 * @return bool True on success, false otherwise
	 */
	public function processJobFinished()
	{
		$config       = JFactory::getConfig();
		$tmpPath      = $config->get('tmp_path');
		$filename     = $this->getFileName();
		$fileContents = json_decode(file_get_contents($tmpPath . '/' . $filename . '/' . $filename . '.json'), true);

		if ($fileContents !== null)
		{
			foreach ($fileContents['strings'] as $translationId => $translationText)
			{
				/* @var $translation NenoContentElementTranslation */
				$translation = NenoContentElementTranslation::load($translationId);
				$translation
					->setString($translationText)
					->persist();
			}

			return true;
		}

		return false;
	}
}
