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
 * Class NenoTaskMonitor
 *
 * @since  1.0
 */
class NenoTaskMonitor
{
	/**
	 * @var integer
	 */
	protected static $maxExecutionTime = null;

	/**
	 * Execute tasks
	 *
	 * @return void
	 */
	public static function runTask()
	{
		// Calculate execution time
		self::calculateMaxExecutionTime();
		$timeRemaining = self::$maxExecutionTime;
		$task          = self::fetchTask();

		// Clean the queue
		self::cleanUp();

		// Execute tasks until we spend all the time
		while ($timeRemaining > 0)
		{
			$iniTime = time();
			$task    = self::fetchTask();

			// If there are task to execute, let's run it
			if (!empty($task))
			{
				$task->execute();

				NenoLog::add($task->getTask() . ' task has been executed properly');
				$task->remove();
			}

			$timeRemaining -= time() - $iniTime;
		}
	}

	/**
	 * Calculate maximum execution time
	 *
	 * @return void
	 */
	protected static function calculateMaxExecutionTime()
	{
		if (self::$maxExecutionTime === null)
		{
			// Setting max_execution_time to 1 hour
			$result = ini_set('max_execution_time', 3600);

			$executionTime = 3600;

			// If no value could be set, let's get the default one.
			if ($result === false)
			{
				$executionTime = ini_get('max_execution_time');
			}

			self::$maxExecutionTime = $executionTime * 0.9;
		}
	}

	/**
	 * Load a task from the queue
	 *
	 * @return NenoTask
	 */
	protected static function fetchTask()
	{
		// Load tasks that hasn't started yet and they have less than 4 attempts
		$task = NenoTask::load(
			array (
				'attemps_filter' => array (
					'_field'     => 'number_of_attempts',
					'_condition' => '<=',
					'_value'     => 3
				),
				'time_started'   => '0000-00-00 00:00:00',
				'_order'         => array (
					'time_added' => 'ASC'
				),
				'_limit'         => 1
			)
		);

		return $task;
	}

	/**
	 * Clean up task queue
	 *
	 * @return void
	 */
	private static function cleanUp()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->update('#__neno_tasks')
			->set('time_started = ' . $db->quote('0000-00-00 00:00:00'));

		$db->setQuery($query);
		$db->execute();
	}

	/**
	 * Add a task to the database
	 *
	 * @param   string $task     Task name (type)
	 * @param   array  $taskData Task Data
	 *
	 * @return bool
	 */
	public static function addTask($task, array $taskData)
	{
		$task = new NenoTask(
			array (
				'task'     => $task,
				'taskData' => $taskData
			)
		);

		return $task->persist();
	}
}
