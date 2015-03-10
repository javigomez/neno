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
 * Class NenoWorker
 *
 * @since  1.0
 */
abstract class NenoTaskWorker
{
	/**
	 * Execute the task
	 *
	 * @param   array|null $taskData Task data
	 *
	 * @return bool True on success, false otherwise
	 */
	abstract public function run($taskData);
}
