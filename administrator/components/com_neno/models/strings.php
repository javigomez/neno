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
 * NenoModelGroupsElements class
 *
 * @since  1.0
 */
class NenoModelStrings extends JModelList
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
	 * Get elements
	 *
	 * @return array
	 */
	public function getItems()
	{
		$elements     = parent::getItems();
		$translations = array ();

		foreach ($elements as $element)
		{
			$translation    = new NenoContentElementTranslation($element, false);
			$translations[] = $translation->prepareDataForView(true);
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

		$groups = $app->getUserState($this->context . '.group', array ());

		if (!empty($groups))
		{
			$this->setState('filter.group_id', $groups);
		}

		// Element(s) filtering
		$elements = $app->getUserState($this->context . '.element', array ());

		if (!empty($elements))
		{
			$this->setState('filter.element', $elements);
		}

		// Field(s) filtering
		$fields = $app->getUserState($this->context . '.field', array ());

		if (!empty($fields))
		{
			$this->setState('filter.field', $fields);
		}

		// Status filtering
		$status = $app->getUserState($this->context . '.translation_status', array ());

		if (!empty($status))
		{
			$this->setState('filter.translation_status', $status);
		}

		// Translation methods filtering
		$method = $app->getUserState($this->context . '.translator_type', array ());

		if (!empty($method))
		{
			$this->setState('filter.translator_type', $method);
		}

		// Offset
		//$group = $app->getUserStateFromRequest('list_limit', 'limit', 0, 'int');
		$this->setState('limit', $app->getUserState('limit', 20));
		$this->setState('limitStart', $app->getUserState('limitStart', 0));

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
		$dbStrings           = parent::getListQuery();
		$languageFileStrings = parent::getListQuery();

		$dbStrings
			->select('tr1.*')
			->from('`#__neno_content_element_translations` AS tr1')
			->innerJoin('`#__neno_content_element_fields` AS f ON tr1.content_id = f.id')
			->innerJoin('`#__neno_content_element_tables` AS t ON t.id = f.table_id')
			->innerJoin('`#__neno_content_element_groups` AS g1 ON t.group_id = g1.id ')
			->innerJoin('`#__neno_content_element_groups_x_translation_methods` AS gtm1 ON g1.id = gtm1.group_id')
			->where(
				array (
					'tr1.language = ' . $db->quote($workingLanguage),
					'tr1.content_type = ' . $db->quote('db_string'),
					'f.translate = 1',
					'gtm1.lang = ' . $db->quote($workingLanguage)
				)
			)->order('tr1.id');

		$languageFileStrings
			->select('tr2.*')
			->from('`#__neno_content_element_translations` AS tr2')
			->innerJoin('`#__neno_content_element_language_strings` AS ls ON tr2.content_id = ls.id')
			->innerJoin('`#__neno_content_element_language_files` AS lf ON lf.id = ls.languagefile_id')
			->innerJoin('`#__neno_content_element_groups` AS g2 ON lf.group_id = g2.id ')
			->leftJoin('`#__neno_content_element_groups_x_translation_methods` AS gtm2 ON g2.id = gtm2.group_id')
			->where(
				array (
					'tr2.language = ' . $db->quote($workingLanguage),
					'tr2.content_type = ' . $db->quote('lang_string'),
					'gtm2.lang = ' . $db->quote($workingLanguage)
				)
			)->order('tr2.id');

		$queryWhereDb = array ();


		/* @var $groups array */
		$groups = $this->getState('filter.group_id', array ());

		/* @var $element array */
		$element = $this->getState('filter.element', array ());

		/* @var $field array */
		$field = $this->getState('filter.field', array ());

		if (!is_array($groups))
		{
			$groups = array ($groups);
		}

		if (!empty($groups))
		{
			$queryWhereDb[] = 't.group_id IN (' . implode(', ', $groups) . ')';
			$languageFileStrings->where('lf.group_id IN (' . implode(', ', $groups) . ')');
		}

		if (!empty($element))
		{
			$queryWhereDb[] = 't.id IN (' . implode(', ', $element) . ')';
		}

		if (!empty($field))
		{
			$queryWhereDb[] = 'f.id IN (' . implode(', ', $field) . ')';
		}

		if (count($queryWhereDb))
		{
			$dbStrings->where('(' . implode(' OR ', $queryWhereDb) . ')');
		}

		$method = (array) $this->getState('filter.translator_type', array ());

		if (count($method))
		{
			$dbStrings->where('gtm1.translation_method_id IN ("' . implode('", "', $method) . '")');
			$languageFileStrings->where('gtm2.translation_method_id IN ("' . implode('", "', $method) . '")');
		}

		$status = (array) $this->getState('filter.translation_status', array ());

		if (count($status) && $status[0] !== '')
		{
			$dbStrings->where('tr1.state IN (' . implode(', ', $status) . ')');
			$languageFileStrings->where('tr2.state IN (' . implode(', ', $status) . ')');
		}

		$limit  = $this->getState('limit', 20);
		$offset = $this->getState('limitStart', 0);

		$query = parent::getListQuery();

		$query
			->select('*')
			->from('((' . (string) $dbStrings . ') UNION (' . (string) $languageFileStrings . ')) AS a');

		$query->setLimit($limit, $offset);

		return $query;
	}
}
