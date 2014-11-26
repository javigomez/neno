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
class LingoTableExtension extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  $db  A database connector object
	 */
	public function __construct($db)
	{
		parent::__construct('#__extensions', 'extension_id', $db);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param   integer|null  $pk  Value of the primary key
	 *
	 * @return bool
	 */
	public function delete($pk = null)
	{
		throw new BadMethodCallException('Operation not allowed');
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param   bool  $updateNulls  True to update fields even if they are null
	 *
	 * @return bool|void
	 */
	public function store($updateNulls = false)
	{
		throw new BadMethodCallException('Operation not allowed');
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param mixed  $src
	 * @param string $orderingFilter
	 * @param string $ignore
	 *
	 * @return bool|void
	 */
	public function save($src, $orderingFilter = '', $ignore = '')
	{
		throw new BadMethodCallException('Operation not allowed');
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param int    $delta
	 * @param string $where
	 *
	 * @return mixed|void
	 */
	public function move($delta, $where = '')
	{
		throw new BadMethodCallException('Operation not allowed');
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param null $pks
	 * @param int  $state
	 * @param int  $userId
	 *
	 * @return bool|void
	 */
	public function publish($pks = null, $state = 1, $userId = 0)
	{
		throw new BadMethodCallException('Operation not allowed');
	}

}
