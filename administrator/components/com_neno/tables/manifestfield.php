<?php
/**
 * @package     Neno
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
 * @todo Remove references to Manifest
 */
class NenoTableManifestField extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  $db  A database connector object
	 */
	public function __construct($db)
	{
		parent::__construct('#__neno_content_elements_fields', 'id', $db);
	}
}
