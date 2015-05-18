<?php

// No direct access
defined('_JEXEC') or die;

/**
 * test Table class
 *
 * @since  1.0
 */
class NenoTableSetting extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabase $db A database connector object
	 */
	public function __construct($db)
	{
		parent::__construct('#__neno_settings', 'id', $db);
	}
}
