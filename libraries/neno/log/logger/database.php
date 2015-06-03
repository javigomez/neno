<?php

/**
 * @package     Neno
 * @subpackage  Log
 *
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('JPATH_NENO') or die;

/**
 * Class NenoLogDatabase
 *
 * @since  1.0
 */
class NenoLogLoggerDatabase extends JLogLoggerDatabase
{
	/**
	 * {@inheritdoc}
	 *
	 * @param   array $options Options
	 */
	public function __construct(array $options)
	{
		$options['db_table'] = '#__neno_log_entries';

		parent::__construct($options);
	}
}
