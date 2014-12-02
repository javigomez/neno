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
 * Class NenoLogApi
 *
 * @since  1.0
 */
class NenoLogApi extends JLogLogger
{
	/**
	 *
	 * @var JHttp
	 */
	protected $httpClient;

	public function __construct(array &$options)
	{
		parent::__construct($options);

		$this->httpClient = new JHttp;
	}

	/**
	 * Method to add an entry to the log.
	 *
	 * @param   JLogEntry $entry The log entry object to add to the log.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	public function addEntry(JLogEntry $entry)
	{
	}
}