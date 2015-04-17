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
 * Class NenoTask
 *
 * @since  1.0
 */
class NenoTask extends NenoObject
{
	/**
	 * @var string
	 */
	protected $task;

	/**
	 * @var Datetime
	 */
	protected $timeAdded;

	/**
	 * @var Datetime
	 */
	protected $timeStarted;

	/**
	 * @var integer
	 */
	protected $numberOfAttempts;

	/**
	 * @var array
	 */
	protected $taskData;

	/**
	 * Generate an id for a new record
	 *
	 * @return mixed
	 */
	public function generateId()
	{
		NenoLog::log('New task added', 2);

		return null;
	}

	/**
	 * Get task
	 *
	 * @return string
	 */
	public function getTask()
	{
		return $this->task;
	}

	/**
	 * Get the time when this task was added
	 *
	 * @return Datetime
	 */
	public function getTimeAdded()
	{
		return $this->timeAdded;
	}

	/**
	 * Get the time when this task started
	 *
	 * @return Datetime
	 */
	public function getTimeStarted()
	{
		return $this->timeStarted;
	}

	/**
	 * Get how many attemps has happened.
	 *
	 * @return int
	 */
	public function getNumberOfAttempts()
	{
		return $this->numberOfAttempts;
	}

	/**
	 * Get task data.
	 *
	 * @return array
	 */
	public function getTaskData()
	{
		return $this->taskData;
	}

	/**
	 * Execute task
	 *
	 * @return void
	 */
	public function execute()
	{
		// Set the time when the task started
		$this->timeStarted = new DateTime;

		// Increase the number of attemps
		$this->numberOfAttempts++;

		// Save this task on the database
		$this->persist();

		// Get and execute the task through a worker.
		$worker = $this->getWorker();

		NenoLog::log('Executing task', 2);

		$worker->run($this->taskData);
	}

	/**
	 * Get Worker related to a task
	 *
	 * @return NenoTaskWorker
	 */
	protected function getWorker()
	{
		// Generate Worker class name
		$className = 'NenoTaskWorker' . ucfirst(NenoHelper::convertDatabaseColumnNameToPropertyName($this->task));

		// Check if the class exists, if it doesn't, let's try to load it.
		if (class_exists($className))
		{
			$worker = new $className;

			return $worker;
		}
		else
		{
			NenoLog::log('Worker not found for this task', 1);

			throw new UnexpectedValueException('Worker not found for this task');
		}
	}
}
