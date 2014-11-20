<?php

/**
 * @version     1.0.0
 * @package     com_lingo
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Soren Beck Jensen <soren@notwebdesign.com> - http://www.notwebdesign.com
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of Lingo records.
 */
class LingoModelTranslatableFields extends JModelList
{

	protected function getListQuery()
	{
		$query = parent::getListQuery();

		$query->select(
			array(
				'id',
				'table_id',
				'field',
				'translate'

			)
		)
			->from('#__lingo_table_fields_information');

		// Check if table id has been passed
		$tableId = $this->getState('table.id', null);

		if (!empty($tableId))
		{
			$query->where('table_id = ' . intval($tableId));
		}

		return $query;
	}

	/**
	 * Method for loading all the fields related to a table marked as translatable
	 *
	 * @param   integer $tableId Table Id
	 *
	 * @return array
	 *
	 * @since 1.0
	 */
	public function getTranslatableTableFields($tableId)
	{
		$this->setState('table.id', $tableId);
		$fields = $this->getItems();

		return $fields;

	}

}
