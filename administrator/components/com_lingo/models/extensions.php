<?php
/**
 * @package     Lingo
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
 * Methods supporting a list of Lingo records.
 *
 * @since  1.0
 */
class LingoModelExtensions extends JModelList
{
	/**
	 * @var array
	 */
	private static $extensionTypeAllowed = array(
		'component',
		'module',
		'plugin',
		'template'
	);

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
					'e.extension_id',
					'e.name',
					'e.type',
					'e.folder',
					'e.enabled'
				)
			)
			->from('`#__extensions` AS e')
			->where('type IN (' . implode(',', array_map(array( 'LingoModelExtensions', 'escapeString' ), self::$extensionTypeAllowed)) . ')')
			->order('name');

		return $query;
	}

	/**
	 * Escape a string
	 *
	 * @param   mixed $value Value
	 *
	 * @return string
	 */
	private static function escapeString($value)
	{
		return JFactory::getDbo()->quote($value);
	}

	/**
	 * Get an array of items
	 *
	 * @return array
	 */
	public function getItems()
	{
		$this->setState('list.limit', 0);

		// Load all the tables saved
		$items = parent::getItems();

		for ($i = 0; $i < count($items); $i++)
		{
			$items[$i]->tables = $this->getComponentTables($items[$i]->name);
		}

		return $items;
	}

	/**
	 * Get all the existing tables in the database
	 *
	 * @param   string|null $type Extension type or null no filter will be applied.
	 *
	 * @return array
	 */
	public
	function getExtensionsByType($type = null)
	{
		$this->setState('extension.type', $type);
		$extensions = $this->getItems();

		return $extensions;
	}

	/**
	 * Get all the tables of the component that matches with the Joomla naming convention.
	 *
	 * @param   string $componentName Component name
	 *
	 * @return array
	 */
	public
	function getComponentTables($componentName)
	{
		$tablePattern = $this->unifyComponentTablesName($componentName);

		$db    = JFactory::getDbo();
		$query = 'SHOW TABLES LIKE ' . $db->quote($tablePattern . '%');
		$db->setQuery($query);
		$tables = $db->loadColumn();

		/* @var $manifestTableModel LingoModelManifestTable */
		$manifestTableModel = LingoHelper::getModel('ManifestTable');

		$result = array();

		for ($i = 0; $i < count($tables); $i++)
		{
			// Get Table name
			$tableName = $manifestTableModel->unifyTableName($tables[$i]);

			$tableData = $manifestTableModel->getItem(array( 'table_name' => $tableName ));

			// Create the object that will store the data.
			if ($tableData->get('id') === null)
			{
				$tableData->set('table_name', $tableName);
				$tableData->set('extension', $componentName);
				$tableData->set('primary_key', $manifestTableModel->getPrimaryKey($tableName));
			}

			$tableData->set('fields', $manifestTableModel->getDatabaseTableColumns($tableData));

			$result[] = $tableData;
		}

		return $result;
	}

	/**
	 * Following Joomla convention, the tables for the component, if its name is com_example, would be #__example
	 *
	 * @param   string $componentName Component name
	 *
	 * @return string
	 */
	private
	function unifyComponentTablesName($componentName)
	{
		$prefix = JFactory::getDbo()->getPrefix();

		return $prefix . str_replace(array( 'com_' ), '', strtolower($componentName));
	}

	/**
	 * Get all the extensions marked as translatable
	 *
	 * @return array
	 */
	public
	function getExtensionsMarkedAsTranslatable()
	{
		$db = JFactory::getDbo();

		$query = $this->getListQuery();
		$query->innerJoin('#__lingo_manifest_tables AS mt ON mt.extension = e.name');
		$db->setQuery($query);

		$extensions = $db->loadObjectList('', 'JObject');

		/* @var $manifestTablesModel LingoModelManifestTables */
		$manifestTablesModel = LingoHelper::getModel('ManifestTables');

		$result = array();

		for ($i = 0; $i < count($extensions); $i++)
		{
			$result[$extensions[$i]->name] = $manifestTablesModel->getManifestTablesByExtensionName($extensions[$i]->get('name'));
		}

		return $result;
	}
}
