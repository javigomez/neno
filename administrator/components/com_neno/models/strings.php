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
		$groups       = array ();

		foreach ($elements as $element)
		{
			if (!empty($element->group_id))
			{
				$groups[] = $element->group_id;
			}

			$translation    = new NenoContentElementTranslation($element, false);
			$translations[] = $translation->prepareDataForView(true);
		}

		if (!empty($groups))
		{
			$this->setState('filter.parent_group_id', $groups);
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

		$groups = $app->getUserStateFromRequest($this->context . '.group', 'group', array ());

		if (!empty($groups))
		{
			$this->setState('filter.group_id', $groups);
		}

		// Element(s) filtering
		$elements = $app->getUserStateFromRequest($this->context . '.table', 'table', array ());

		if (!empty($elements))
		{
			$app->setUserState($this->context . '.filter.elements', $elements);
		}

		$this->setState('filter.element', $app->getUserState($this->context . '.filter.elements'));

		// Field(s) filtering
		$fields = $app->getUserStateFromRequest($this->context . '.field', 'field', array ());

		if (!empty($fields))
		{
			$this->setState('filter.field', $fields);
		}

		// Status filtering
		$status = $app->getUserStateFromRequest($this->context . '.status', 'status', array ());

		if (!empty($status))
		{
			$this->setState('filter.translation_status', $status);
		}

		// Translation methods filtering
		$method = $app->getUserStateFromRequest($this->context . '.type', 'type', array ());

		if (!empty($method))
		{
			$app->setUserState($this->context . '.filter.translator_type', $method);
		}

		$this->setState('filter.translator_type', $app->getUserState($this->context . '.filter.translator_type'));

		// Offset
		$this->setState('limit', $app->getUserStateFromRequest('limit', 20));
		$this->setState('limitStart', $app->getUserStateFromRequest('limitStart', 0));

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

		if (!empty($groups) && !in_array('none', $groups))
		{
			$queryWhereDb[] = 't.group_id IN (' . implode(', ', $groups) . ')';
			$languageFileStrings->where('lf.group_id IN (' . implode(', ', $groups) . ')');
		}

		if (!empty($element))
		{
			$languageFileStrings->select('g2.id AS group_id');
			$dbStrings->select('g1.id AS group_id');
			$queryWhereDb[] = 't.id IN (' . implode(', ', $element) . ')';
		}

		if (!empty($field))
		{
			$languageFileStrings->select('g2.id AS group_id');
			$dbStrings->select('g1.id AS group_id');
			$queryWhereDb[] = 'f.id IN (' . implode(', ', $field) . ')';
		}

		if (count($queryWhereDb))
		{
			$dbStrings->where('(' . implode(' OR ', $queryWhereDb) . ')');
		}

		$method = (array) $this->getState('filter.translator_type', array ());

		if (!empty($method) && !in_array('none', $method))
		{
			$dbStrings->where('gtm1.translation_method_id IN ("' . implode('", "', $method) . '")');
			$languageFileStrings->where('gtm2.translation_method_id IN ("' . implode('", "', $method) . '")');
		}

		$status = (array) $this->getState('filter.translation_status', array ());

		if (!empty($status) && $status[0] !== '' && !in_array('none', $status))
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
