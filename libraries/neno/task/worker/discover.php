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
 * Class NenoTaskWorkerScan
 *
 * @since  1.0
 */
class NenoTaskWorkerDiscover extends NenoTaskWorker
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
		/* @var $db NenoDatabaseDriverMysqlx */
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$extensions = $db->quote(NenoHelper::whichExtensionsShouldBeTranslated());

		$query
			->select('e.*')
			->from('`#__extensions` AS e')
			->where(
				array (
					'e.type IN (' . implode(',', $extensions) . ')',
					'e.name NOT LIKE \'com_neno\'',
					'NOT EXISTS (SELECT 1 FROM #__neno_content_element_groups_x_extensions AS ge WHERE ge.extension_id = e.extension_id)'
				)
			)
			->order('name');
		$db->setQuery($query);
		$extensions = $db->loadAssocList();

		foreach ($extensions as $extension)
		{
			NenoHelper::discoverExtension($extension);
		}

		return true;

	}
}
