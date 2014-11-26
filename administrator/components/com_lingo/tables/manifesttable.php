<?php
/**
 * @package     Lingo
 * @subpackage  Tables
 *
 * @author      Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * source Table class
 *
 * @since  1.0
 */
class LingoTableManifestTable extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver $db A database connector object
	 */
	public function __construct($db)
	{
		parent::__construct('#__lingo_manifest_tables', 'id', $db);
	}

	/**
	 * Clear all the field that has not been marked as translatable but they were marked before
	 *
	 * @param   integer  $tableId      Table Id
	 * @param   array    $fieldsAdded  Added An array of Id
	 *
	 * @return void
	 *
	 * @since 1.0
	 */
	public function clearUnusedFields($tableId, array $fieldsAdded)
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true);
		$query
			->delete('#__lingo_manifest_fields')
			->where(
				array(
					'table_id = ' . intval($tableId),
					'id NOT IN (' . implode(',', $fieldsAdded) . ')'
				)
			);

		$db->setQuery($query);
		$db->execute();
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param   integer|null $pk Value of the primary key
	 *
	 * @return bool
	 */
	public function delete($pk = null)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query
			->delete('#__lingo_manifest_fields')
			->where('table_id  = ' . $pk);

		$db->setQuery($query);

		try
		{
			$db->execute();

			return parent::delete($pk);
		}
		catch ( RuntimeException $ex )
		{
			return false;
		}
	}
}
