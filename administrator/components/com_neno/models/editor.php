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
	 * Get all the items
	 *
	 * @return array
	 */
	public function getItems()
	{
		/* @var $stringModel NenoModelStrings */
		$stringModel = NenoHelper::getModel('strings');

		return $stringModel->getItems();
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

		// List state information.
		parent::populateState('a.id', 'asc');
	}
}
