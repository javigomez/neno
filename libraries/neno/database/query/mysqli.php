<?php

/**
 * @package     Neno
 * @subpackage  Database
 *
 * @copyright   Copyright (c) 2014 Jensen Technologies S.L. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Class LingoDatabaseQuery
 *
 * @since  1.0
 */
class NenoDatabaseQueryMysqli extends JDatabaseQueryMysqli
{
	/**
	 * @var JDatabaseQueryElement
	 */
	protected $insert = null;

	/**
	 * Set a replace statement
	 *
	 * @param   string $table Table name
	 *
	 * @return NenoDatabaseQueryMysqli
	 */
	public function replace($table)
	{
		$this->type   = 'insert';
		$this->insert = new JDatabaseQueryElement('REPLACE INTO', $table);

		return $this;
	}
}
