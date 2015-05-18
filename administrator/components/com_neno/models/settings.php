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
				'setting_key', 'a.setting_key',
				'setting_value', 'a.setting_value'
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
		$db    = JFactory::getDbo();
		$query = parent::getListQuery();

		$query
			->select('a.*')
			->from('#__neno_settings AS a')
			->where('a.show_settings_screen = 1');


		// Add the list ordering clause.
		$orderCol       = $this->state->get('list.ordering');
		$orderDirection = $this->state->get('list.direction');

		if ($orderCol && $orderDirection)
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirection));
		}

		return $query;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * @return void
	 */
	protected function populateState()
	{
		// List state information.
		parent::populateState('a.setting_key', 'asc');
	}
}
