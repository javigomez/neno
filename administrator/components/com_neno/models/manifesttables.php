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
 * NenoModelManifestTables class
 *
 * @since  1.0
 * @todo Remove references to Manifest
 */
class NenoModelManifestTables extends JModelList
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
			->select(
				array(
					'id',
					'table_name',
					'primary_key',
					'enabled'
				)
			)
			->from('#__neno_content_elements_tables');

		$extensionName = $this->getState('extension.name');

		if (!empty($extensionName))
		{
			$query->where('extension LIKE ' . $db->quote($extensionName));
		}

		return $query;
	}

	/**
	 * Get an array of items
	 *
	 * @return array
	 */
	public function getItems()
	{
		// Load all the tables saved
		$items = parent::getItems();

		// Init result array
		$result = array();

		/* @var $manifestFieldsModel NenoModelManifestFields */
		$manifestFieldsModel = NenoHelper::getModel('ManifestFields');

		foreach ($items as $item)
		{
			$result[$item->table_name] = $manifestFieldsModel->getManifestFieldsByTableId($item->id);
		}

		return $result;
	}

	/**
	 * Delete tables that were marked as translatable, but they are not any more
	 *
	 * @param   array $tableAdded A list of IDs
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function deleteUnusedTables(array $tableAdded)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('id')
			->from('#__neno_content_elements_tables');

		// If the array is not empty, let's add the clause
		if (!empty($tableAdded))
		{
			$query->where('id NOT IN (' . implode(',', $tableAdded) . ')');
		}

		$db->setQuery($query);
		$tablesId = $db->loadObjectList('id');

		/* @var $manifestTableTable NenoTableManifestTable */
		$manifestTableTable = $this->getTable('ManifestTable', 'NenoTable');

		// Delete all the tables that are not needed anymore
		foreach ($tablesId as $tableId)
		{
			$manifestTableTable->delete($tableId->id);
		}
	}

	/**
	 * Get all the manifest tables that belong to a particular extension
	 *
	 * @param   string  $extensionName  Extension name
	 *
	 * @return array
	 */
	public function getManifestTablesByExtensionName($extensionName)
	{
		$this->setState('extension.name', $extensionName);

		return $this->getItems();
	}
}
