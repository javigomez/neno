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

jimport('joomla.application.component.modellist');

/**
 * NenoModelManifestFields class
 *
 * @since  1.0
 */
class NenoModelManifestFields extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array $config An optional associative array of configuration settings.
	 *
	 * @see        JController
	 * @since      1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
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
	 * Get an array of items
	 *
	 * @return array
	 */
	public function getItems()
	{
		$items = parent::getItems();

		return NenoHelper::convertStdClassArrayToJObjectArray($items);
	}

	/**
	 * Method to auto-populate the model state.
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string $ordering  Ordering field
	 * @param   string $direction Ordering direction [ASC,DESC]
	 *
	 * @return void
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_neno');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('a.id', 'asc');
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string $id A prefix for the store id.
	 *
	 * @return    string        A store id.
	 *
	 * @since    1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');

		return parent::getStoreId($id);
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
			->select(
				array(
					'id',
					'table_id',
					'field',
					'enabled'
				)
			)
			->from('#__neno_manifest_fields');

		// Get Table Id from the state
		$tableId = $this->getState('manifestTable.id');

		if (!empty($tableId))
		{
			$query->where('table_id = ' . intval($tableId));
		}

		return $query;
	}

	/**
	 * Get a list of fields based on the table id
	 *
	 * @param integer $tableId TableId
	 *
	 * @return array
	 */
	public function getManifestFieldsByTableId($tableId)
	{
		$this->setState('manifestTable.id', $tableId);

		return $this->getItems();
	}
}
