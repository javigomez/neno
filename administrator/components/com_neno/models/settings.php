<?php
/**
 * @package     Neno
 * @subpackage  Models
 *
 * @author      Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * NenoModelGroupsElements class
 *
 * @since  1.0
 */
class NenoModelSettings extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array $config An optional associative array of configuration settings.
	 *
	 * @see        JController
	 * @since      1.6
	 */
	public function __construct($config = array ())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array (
				'id', 'a.id',
				'string', 'a.string',
				'constant', 'a.constant',
				'lang', 'a.lang',
				'extension', 'a.extension',
				'time_added', 'a.time_added',
				'time_changed', 'a.time_changed',
				'time_deleted', 'a.time_deleted',
				'version', 'a.version',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return    JDatabaseQuery
	 *
	 * @since    1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$query = parent::getListQuery();

		$query
			->select('a.*')
			->from('#__neno_settings AS a')
			->where('a.show_settings_screen = 1');

		return $query;
	}
}
