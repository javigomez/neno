<?php
/**
 * @package     Neno
 * @subpackage  Controllers
 *
 * @author      Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * Manifest Groups & Elements controller class
 *
 * @since  1.0
 */
class NenoControllerSetting extends JControllerForm
{
	/**
	 * Constructor
	 *
	 * @param   array $config Constructor configuration
	 *
	 * @throws Exception
	 */
	public function __construct($config = array ())
	{
		$this->view_list = 'groupselements';
		parent::__construct($config);
	}
}

