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
 * NenoModelEditor class
 *
 * @since  1.0
 */
class NenoModelEditor extends JModelList
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
			$config['filter_fields'] = array (/*'id', 'a.id',
				'string', 'a.string',
				'constant', 'a.constant',
				'lang', 'a.lang',
				'extension', 'a.extension',
				'time_added', 'a.time_added',
				'time_changed', 'a.time_changed',
				'time_deleted', 'a.time_deleted',
				'version', 'a.version',*/
			);
		}

		parent::__construct($config);
	}

	/**
	 * Get all the existing tables in the database
	 *
	 * @param   string|null $type Extension type or null no filter will be applied.
	 *
	 * @return array
	 */
	public function getExtensionsByType($type = null)
	{
		$this->setState('extension.type', $type);
		$extensions = $this->getItems();

		return $extensions;
	}

	public function getItems()
	{
		$elements = parent::getItems();

		//var_dump($elements);

		$translations = array ();

		$countElements = count($elements);

		for ($i = 0; $i < $countElements; $i++)
		{
			$translations[] = new NenoContentElementTranslation($elements[$i]);

			/*$element = NenoContentElementField::getFieldById($elements[$i]->id);
			if (!empty($element)) {
				$translations[$i] = NenoContentElementTranslation::getTranslations($element);

			}*/
		}

		//var_dump($elements);
		return $translations;
	}

	/**
	 * Get and set current values of filters
	 *
	 * @param
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');


		// Other code goes here


		$group = $app->getUserStateFromRequest($this->context . 'filter.group_id', 'filter_group_id', '', 'string');
		$this->setState('filter.group_id', $group);


		// Other code goes here


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
		$filterJson     = $this->getState('filter.multiselect-value', '[]');
		$filterArray    = json_decode($filterJson);
		$filterGroups   = array ();
		$filterElements = array ();
		$filterKeys     = array ();

		foreach ($filterArray as $filterItem)
		{
			if (strpos($filterItem, 'group') !== false)
			{
				$filterGroups[] = str_replace('group', '', $filterItem);
			}
			elseif (strpos($filterItem, 'table') !== false)
			{
				$filterElements[] = str_replace('table', '', $filterItem);
			}
			elseif (strpos($filterItem, 'field') !== false)
			{
				$filterField[] = str_replace('field', '', $filterItem);
			}
		}

		$workingLanguage = NenoHelper::getWorkingLanguage();

		NenoLog::log('Querying #__neno_content_element_tables from getListQuery of NenoModelEditor', 3);

		// Create a new query object.
		$query = parent::getListQuery();

		$query->select('tr.*');
		$query->from('`#__neno_content_element_tables` AS t');
		$query->leftJoin('`#__neno_content_element_fields` AS f ON t.id = f.table_id AND f.translate = 1');
		$query->leftJoin('`#__neno_content_element_translations` AS tr ON tr.content_id = f.id');
		$query->where('tr.language = "' . $workingLanguage . '"');

		$queryWhere = array ();

		if (count($filterGroups))
		{
			$queryWhere[] = 't.group_id IN (' . implode(', ', $filterGroups) . ')';
		}

		if (count($filterElements))
		{
			$queryWhere[] = 't.id IN (' . implode(', ', $filterElements) . ')';
		}

		if (count($filterKeys))
		{
			$queryWhere[] = 'f.id IN (' . implode(', ', $filterKeys) . ')';
		}

		if (count($queryWhere))
		{
			$query->where('(' . implode(' OR ', $queryWhere) . ')');
		}

		return $query;
	}
}