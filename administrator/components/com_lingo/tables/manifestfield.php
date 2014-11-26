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
class LingoTableManifestField extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  $db  A database connector object
	 */
	public function __construct($db)
	{
		parent::__construct('#__lingo_manifest_fields', 'id', $db);
	}
}
