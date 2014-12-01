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
 * NenoModelTranslatableFields class
 *
 * @since  1.0
 */
class NenoModelTranslatableFields extends JModelList
{
	/**
	 * {@inheritDoc}
	 *
	 * @return JDatabaseQuery
	 */
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
			->from('#__neno_table_fields_information');

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
	 * @param   integer  $tableId  Table Id
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
