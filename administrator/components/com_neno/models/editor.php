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
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(/*'id', 'a.id',
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
		$elements     = parent::getItems();
		$translations = array();

		foreach ($elements as $element)
		{
			$translation    = new NenoContentElementTranslation($element);
			$translations[] = $translation->prepareDataForView();
		}

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
		$app = JFactory::getApplication();


		// Group(s) filtering
		$group = $app->getUserStateFromRequest($this->context . 'filter.group_id', 'filter_group_id', '', 'string');

		if (!empty($group))
		{
			$this->setState('filter.group_id', $group);
		}

		$groups = $app->getUserState($this->context . '.group', array());

		if (!empty($groups))
		{
			$this->setState('filter.group_id', $groups);
		}

		// Element(s) filtering
		$elements = $app->getUserState($this->context . '.element', array());

		if (!empty($elements))
		{
			$this->setState('filter.element', $elements);
		}

		// Field(s) filtering
		$fields = $app->getUserState($this->context . '.field', array());

		if (!empty($fields))
		{
			$this->setState('filter.field', $fields);
		}

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
		$db              = JFactory::getDbo();
		$workingLanguage = NenoHelper::getWorkingLanguage();

		NenoLog::log('Querying #__neno_content_element_tables from getListQuery of NenoModelStrings', 3);

		// Create a new query object.
		$query = parent::getListQuery();

		$query
			->select('tr.*')
			->from('`#__neno_content_element_tables` AS t')
			->leftJoin('`#__neno_content_element_fields` AS f ON t.id = f.table_id AND f.translate = 1')
			->leftJoin('`#__neno_content_element_translations` AS tr ON tr.content_id = f.id')
			->where('tr.language = ' . $db->quote($workingLanguage));

		$queryWhere = array();

		/* @var $groups array */
		$groups = $this->getState('filter.group_id', array());

		/* @var $element array */
		$element = $this->getState('filter.element', array());

		/* @var $field array */
		$field = $this->getState('filter.field', array());

		if (!is_array($groups))
		{
			$groups = array($groups);
		}

		if (!empty($groups))
		{
			$queryWhere[] = 't.group_id IN (' . implode(', ', $groups) . ')';
		}


		if (!empty($element))
		{
			$queryWhere[] = 't.id IN (' . implode(', ', $element) . ')';
		}

		if (!empty($field))
		{
			$queryWhere[] = 'f.id IN (' . implode(', ', $field) . ')';
		}

		if (count($queryWhere))
		{
			$query->where('(' . implode(' OR ', $queryWhere) . ')');
		}

		$method = $this->getState('filter.translator_type', '');
		if ($method)
		{
			$query->where('translation_method = "' . $method . '"');
		}

		$status = $this->getState('filter.translation_status', '');
		if ($status)
		{
			$query->where('tr.state =' . $status);
		}

		return $query;
	}
}
