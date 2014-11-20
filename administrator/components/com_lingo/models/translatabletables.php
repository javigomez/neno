<?php

/**
 *
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Class LingoModelTranslatableTables
 *
 * @category   Class
 * @package    Joomla
 * @subpackage Lingo
 *
 * @author     Jensen Technologies S.L <info@notwebdesign.com>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
class LingoModelTranslatableTables extends JModelList
{

	/**
	 * {@inheritDoc}
	 */
	protected function getListQuery()
	{
		// Called to the parent method to make easy get the query object
		$query = parent::getListQuery();

		$query->select(
			array(
				'id',
				'table_name',
				'primary_key'
			)
		)
			->from('#__lingo_tables_information');

		return $query;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getItems()
	{
		$items = parent::getItems();

		$structure = array();
		foreach ($items as $item)
		{
			$structure[$this->unifyTableName($item->table_name)] = $item;
		}

		return $structure;
	}

	/**
	 * Get all the tables in the Joomla installation
	 *
	 * @return array
	 *
	 * @since 1.0
	 */
	public function getAllJoomlaTables()
	{
		// Load Database driver
		$db     = JFactory::getDbo();
		$tables = $db->getTableList();

		$structure = array();

		foreach ($tables as $table)
		{
			$columns                                  = $db->getTableColumns($table);
			$structure[$this->unifyTableName($table)] = array_keys($columns);
		}

		return $structure;

	}

	/**
	 * Unify table name to prevents issue if the user changes the database prefix
	 *
	 * @param string $tableName Table name
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	public function unifyTableName($tableName)
	{
		// Load database prefix to unify the name of the table
		$dbPrefix = JFactory::getConfig()->get('dbprefix');

		// Replace database prefix with #_
		return str_replace($dbPrefix, '#__', $tableName);
	}

}
