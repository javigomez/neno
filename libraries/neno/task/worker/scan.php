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
 * Class NenoTaskWorkerScan
 *
 * @since  1.0
 */
class NenoTaskWorkerScan extends NenoTaskWorker
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
		/* @var $group NenoContentElementGroup */
		$group = NenoContentElementGroup::load(array ('_order' => array ('id' => 'asc'), '_limit' => 1, '_offset' => $taskData['group'] - 1));
		$group->refresh();

		NenoTaskMonitor::addTask('scan', array ('group' => $taskData['group'] + 1));
	}
}
